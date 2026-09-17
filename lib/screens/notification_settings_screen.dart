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
    return Scaffold(
      backgroundColor: const Color(0xFFF4F6FC),
      appBar: AppBar(
        backgroundColor: const Color(0xFF001540),
        elevation: 0,
        systemOverlayStyle: SystemUiOverlayStyle.light,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 18),
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
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFF002366)),
            )
          : ListView(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 40),
              children: [
                // Master Switch Card
                _buildMasterSwitchCard(),
                const SizedBox(height: 24),

                // Channels Section
                _buildSectionHeader('In-App Alerts'),
                const SizedBox(height: 10),
                _buildCardContainer([
                  _buildToggleRow(
                    title: 'Request Status Updates',
                    subtitle: 'Receive alerts when requests move to Under Review, Approved, or Rejected.',
                    icon: Icons.assignment_outlined,
                    value: _statusUpdates && _pushEnabled,
                    enabled: _pushEnabled,
                    onChanged: (v) => _updateSetting(
                      _keyStatusUpdates,
                      v,
                      (val) => _statusUpdates = val,
                    ),
                  ),
                  _buildDivider(),
                  _buildToggleRow(
                    title: 'Direct Messages & Comments',
                    subtitle: 'Get notified when the Marketing Office sends a message or note.',
                    icon: Icons.chat_bubble_outline_rounded,
                    value: _directMessages && _pushEnabled,
                    enabled: _pushEnabled,
                    onChanged: (v) => _updateSetting(
                      _keyDirectMessages,
                      v,
                      (val) => _directMessages = val,
                    ),
                  ),
                  _buildDivider(),
                  _buildToggleRow(
                    title: 'Calendar & Posting Reminders',
                    subtitle: 'Alerts on the scheduled day when your approved post goes live.',
                    icon: Icons.calendar_today_outlined,
                    value: _scheduleReminders && _pushEnabled,
                    enabled: _pushEnabled,
                    onChanged: (v) => _updateSetting(
                      _keyScheduleReminders,
                      v,
                      (val) => _scheduleReminders = val,
                    ),
                  ),
                ]),

                const SizedBox(height: 24),

                // External Channel Section
                _buildSectionHeader('Email Notifications'),
                const SizedBox(height: 10),
                _buildCardContainer([
                  _buildToggleRow(
                    title: 'Email Status Digest',
                    subtitle: 'Send official status summaries to your verified university email.',
                    icon: Icons.mail_outline_rounded,
                    value: _emailSummary,
                    enabled: true,
                    onChanged: (v) => _updateSetting(
                      _keyEmailSummary,
                      v,
                      (val) => _emailSummary = val,
                    ),
                  ),
                ]),

                const SizedBox(height: 24),

                // Information Note
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0F4FC),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFD9E2F2)),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: const [
                      Icon(
                        Icons.info_outline_rounded,
                        size: 20,
                        color: Color(0xFF002366),
                      ),
                      SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          'Important security alerts and password reset notifications will always be sent regardless of notification preferences.',
                          style: TextStyle(
                            fontFamily: 'DM Sans',
                            fontSize: 12,
                            color: Color(0xFF4B5563),
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

  Widget _buildMasterSwitchCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF001540), Color(0xFF002366)],
        ),
        borderRadius: BorderRadius.circular(20),
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

  Widget _buildSectionHeader(String title) {
    return Text(
      title,
      style: const TextStyle(
        fontFamily: 'DM Sans',
        fontSize: 13,
        fontWeight: FontWeight.w800,
        color: Color(0xFF6B7280),
        letterSpacing: 0.4,
      ),
    );
  }

  Widget _buildCardContainer(List<Widget> children) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE4E8F0)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 8,
            offset: Offset(0, 2),
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
              color: enabled ? const Color(0xFFF0F4FC) : const Color(0xFFF3F4F6),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              icon,
              size: 20,
              color: enabled ? const Color(0xFF002366) : const Color(0xFF9CA3AF),
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
                    color: enabled ? const Color(0xFF080F1E) : const Color(0xFF9CA3AF),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 11.5,
                    color: enabled ? const Color(0xFF6B7280) : const Color(0xFF9CA3AF),
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          Switch(
            value: value,
            activeColor: const Color(0xFF002366),
            activeTrackColor: const Color(0xFF90B0FF),
            onChanged: enabled ? onChanged : null,
          ),
        ],
      ),
    );
  }

  Widget _buildDivider() {
    return Container(
      height: 1,
      margin: const EdgeInsets.symmetric(horizontal: 16),
      color: const Color(0xFFF0F4FC),
    );
  }
}
