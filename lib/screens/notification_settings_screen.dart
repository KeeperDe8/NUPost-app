import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../widgets/app_snackbar.dart';

class NotificationSettingsScreen extends StatefulWidget {
  const NotificationSettingsScreen({super.key});

  @override
  State<NotificationSettingsScreen> createState() =>
      _NotificationSettingsScreenState();
}

class _NotificationSettingsScreenState extends State<NotificationSettingsScreen> {
  bool _isLoading = true;

  // Setting keys
  static const _keyPushEnabled = 'notif_push_enabled';
  static const _keyStatusUpdates = 'notif_status_updates';
  static const _keyDirectMessages = 'notif_direct_messages';
  static const _keyScheduleReminders = 'notif_schedule_reminders';
  static const _keyEmailSummary = 'notif_email_summary';

  // Toggle values
  bool _pushEnabled = true;
  bool _statusUpdates = true;
  bool _directMessages = true;
  bool _scheduleReminders = true;
  bool _emailSummary = false;

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      setState(() {
        _pushEnabled = prefs.getBool(_keyPushEnabled) ?? true;
        _statusUpdates = prefs.getBool(_keyStatusUpdates) ?? true;
        _directMessages = prefs.getBool(_keyDirectMessages) ?? true;
        _scheduleReminders = prefs.getBool(_keyScheduleReminders) ?? true;
        _emailSummary = prefs.getBool(_keyEmailSummary) ?? false;
        _isLoading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _updateSetting(String key, bool value, Function(bool) updater) async {
    setState(() => updater(value));
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool(key, value);
      if (mounted) {
        AppSnackbar.show(
          context,
          'Preferences updated successfully.',
          isSuccess: true,
        );
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0A0F1D) : const Color(0xFFF4F6FC),
      appBar: AppBar(
        backgroundColor: isDark ? const Color(0xFF0A0F1D) : const Color(0xFF001540),
        elevation: 0,
        systemOverlayStyle: SystemUiOverlayStyle.light,
        leading: IconButton(
          icon: Icon(
            Icons.arrow_back_ios_new_rounded,
            color: isDark ? const Color(0xFFFFD200) : Colors.white,
            size: 18,
          ),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text(
          'Notification Settings',
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w800,
            fontSize: 17,
            color: Colors.white,
          ),
        ),
        centerTitle: true,
      ),
      body: _isLoading
          ? Center(
              child: CircularProgressIndicator(
                color: isDark ? const Color(0xFFFFD200) : const Color(0xFF002366),
              ),
            )
          : ListView(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 40),
              children: [
                // Master Switch Card
                _buildMasterSwitchCard(isDark),
                const SizedBox(height: 24),

                // Channels Section
                _buildSectionHeader('In-App Alerts', isDark),
                const SizedBox(height: 10),
                _buildCardContainer([
                  _buildToggleRow(
                    title: 'Request Status Updates',
                    subtitle: 'Receive alerts when requests move to Under Review, Approved, or Rejected.',
                    icon: Icons.assignment_outlined,
                    value: _statusUpdates && _pushEnabled,
                    enabled: _pushEnabled,
                    isDark: isDark,
                    onChanged: (v) => _updateSetting(
                      _keyStatusUpdates,
                      v,
                      (val) => _statusUpdates = val,
                    ),
                  ),
                  _buildDivider(isDark),
                  _buildToggleRow(
                    title: 'Direct Messages & Comments',
                    subtitle: 'Get notified when the Marketing Office sends a message or note.',
                    icon: Icons.chat_bubble_outline_rounded,
                    value: _directMessages && _pushEnabled,
                    enabled: _pushEnabled,
                    isDark: isDark,
                    onChanged: (v) => _updateSetting(
                      _keyDirectMessages,
                      v,
                      (val) => _directMessages = val,
                    ),
                  ),
                  _buildDivider(isDark),
                  _buildToggleRow(
                    title: 'Calendar & Posting Reminders',
                    subtitle: 'Alerts on the scheduled day when your approved post goes live.',
                    icon: Icons.calendar_today_outlined,
                    value: _scheduleReminders && _pushEnabled,
                    enabled: _pushEnabled,
                    isDark: isDark,
                    onChanged: (v) => _updateSetting(
                      _keyScheduleReminders,
                      v,
                      (val) => _scheduleReminders = val,
                    ),
                  ),
                ], isDark),

                const SizedBox(height: 24),

                // External Channel Section
                _buildSectionHeader('Email Notifications', isDark),
                const SizedBox(height: 10),
                _buildCardContainer([
                  _buildToggleRow(
                    title: 'Email Status Digest',
                    subtitle: 'Send official status summaries to your verified university email.',
                    icon: Icons.mail_outline_rounded,
                    value: _emailSummary,
                    enabled: true,
                    isDark: isDark,
                    onChanged: (v) => _updateSetting(
                      _keyEmailSummary,
                      v,
                      (val) => _emailSummary = val,
                    ),
                  ),
                ], isDark),

                const SizedBox(height: 24),

                // Information Note
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF0D1527) : const Color(0xFFF0F4FC),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFD9E2F2),
                    ),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Icon(
                        Icons.info_outline_rounded,
                        size: 20,
                        color: isDark ? const Color(0xFFFFD200) : const Color(0xFF002366),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          'Important security alerts and password reset notifications will always be sent regardless of notification preferences.',
                          style: TextStyle(
                            fontFamily: 'DM Sans',
                            fontSize: 12,
                            color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF4B5563),
                            height: 1.45,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildMasterSwitchCard(bool isDark) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: isDark
              ? [const Color(0xFF0D182E), const Color(0xFF132244)]
              : [const Color(0xFF001540), const Color(0xFF002366)],
        ),
        borderRadius: BorderRadius.circular(20),
        border: isDark ? Border.all(color: const Color(0xFF1E2B45), width: 1.2) : null,
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF001540).withOpacity(0.2),
            blurRadius: 14,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.12),
              borderRadius: BorderRadius.circular(14),
            ),
            child: const Icon(
              Icons.notifications_active_rounded,
              color: Color(0xFFFFD200),
              size: 24,
            ),
          ),
          const SizedBox(width: 14),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Push Notifications',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: Colors.white,
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  'Enable or disable all mobile alerts',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 12,
                    color: Color(0xFF90B0FF),
                  ),
                ),
              ],
            ),
          ),
          Switch(
            value: _pushEnabled,
            activeColor: const Color(0xFFFFD200),
            activeTrackColor: const Color(0xFF2B5CE6),
            inactiveThumbColor: Colors.white70,
            inactiveTrackColor: Colors.white24,
            onChanged: (v) => _updateSetting(
              _keyPushEnabled,
              v,
              (val) => _pushEnabled = val,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(String title, bool isDark) {
    return Text(
      title,
      style: TextStyle(
        fontFamily: 'DM Sans',
        fontSize: 13,
        fontWeight: FontWeight.w800,
        color: isDark ? const Color(0xFFFFD200) : const Color(0xFF6B7280),
        letterSpacing: 0.4,
      ),
    );
  }

  Widget _buildCardContainer(List<Widget> children, bool isDark) {
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFE4E8F0),
        ),
        boxShadow: [
          BoxShadow(
            color: isDark ? Colors.black.withOpacity(0.25) : const Color(0x06000000),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(18),
        child: Column(children: children),
      ),
    );
  }

  Widget _buildToggleRow({
    required String title,
    required String subtitle,
    required IconData icon,
    required bool value,
    required bool enabled,
    required bool isDark,
    required ValueChanged<bool> onChanged,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              color: enabled
                  ? (isDark ? const Color(0xFF1E2B45) : const Color(0xFFF0F4FC))
                  : (isDark ? const Color(0xFF111827) : const Color(0xFFF3F4F6)),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              icon,
              size: 20,
              color: enabled
                  ? (isDark ? const Color(0xFFFFD200) : const Color(0xFF002366))
                  : (isDark ? const Color(0xFF64748B) : const Color(0xFF9CA3AF)),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: enabled
                        ? (isDark ? Colors.white : const Color(0xFF080F1E))
                        : (isDark ? const Color(0xFF64748B) : const Color(0xFF9CA3AF)),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 11.5,
                    color: enabled
                        ? (isDark ? const Color(0xFF8E9BAE) : const Color(0xFF6B7280))
                        : (isDark ? const Color(0xFF475569) : const Color(0xFF9CA3AF)),
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          Switch(
            value: value,
            activeColor: isDark ? const Color(0xFFFFD200) : const Color(0xFF002366),
            activeTrackColor: isDark ? const Color(0xFF002D80) : const Color(0xFF90B0FF),
            inactiveThumbColor: isDark ? const Color(0xFF8E9BAE) : null,
            inactiveTrackColor: isDark ? const Color(0xFF1E2B45) : null,
            onChanged: enabled ? onChanged : null,
          ),
        ],
      ),
    );
  }

  Widget _buildDivider(bool isDark) {
    return Container(
      height: 1,
      margin: const EdgeInsets.symmetric(horizontal: 16),
      color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFF0F4FC),
    );
  }
}
