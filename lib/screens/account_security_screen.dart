import 'package:flutter/material.dart';

class AccountSecurityScreen extends StatefulWidget {
  const AccountSecurityScreen({super.key});

  @override
  State<AccountSecurityScreen> createState() => _AccountSecurityScreenState();
}

class _AccountSecurityScreenState extends State<AccountSecurityScreen> {
  bool _isLoading = false;

  void _updatePassword() async {
    setState(() => _isLoading = true);
    await Future.delayed(const Duration(seconds: 1)); // Mock Network
    if (mounted) {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Password updated successfully!')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFE8ECF4),
      appBar: AppBar(
        title: const Text(
          'Account Security',
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w700,
            color: Color(0xFF111827),
          ),
        ),
        backgroundColor: Colors.transparent,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xFF111827)),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Change Password',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontFamily: 'DM Sans',
                      fontSize: 18,
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'Current Password',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontFamily: 'DM Sans',
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextFormField(
                    obscureText: true,
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: Colors.white,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'New Password',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontFamily: 'DM Sans',
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextFormField(
                    obscureText: true,
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: Colors.white,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'Confirm Password',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontFamily: 'DM Sans',
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextFormField(
                    obscureText: true,
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: Colors.white,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                    ),
                  ),
                  const SizedBox(height: 32),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF3B6EF5),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      onPressed: _updatePassword,
                      child: const Text(
                        'Update Password',
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
