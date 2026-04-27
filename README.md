# User Switcher for WordPress

> Switch between any WordPress user account in one click — no password needed. Built for developers, agencies, and site administrators who need to move fast.

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue?logo=wordpress)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.0%2B-777BB4?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-GPLv2-green)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.1.0-orange)](https://wordpress.org/plugins/user-switcher/)

---

## Why User Switcher?

Every WordPress developer knows the pain: you need to check how a page looks for a subscriber, verify that a contributor's dashboard is set up correctly, or reproduce a bug only one user is seeing. The old way meant opening an incognito window, logging out and back in, or keeping a password spreadsheet open.

**User Switcher ends all of that.** Switch to any account in two clicks, work as that user, then jump back to your admin account with a single button — all without ever touching a password.

---

## Features

### Smart User Search
Type a name, email address, or username and results appear instantly. No minimum character requirement — open the modal and all users are right there, ready to scroll.

### Role Filter Tabs
Filter the user list by role in one click. Want to switch to an Editor? Click the Editor tab. Looking for a specific Subscriber? Filter and search at the same time.

### Recent Users
The five users you switched to most recently appear at the top of the modal. Coming back to the same test account? It's always one click away.

### Beautiful User Cards
Every user is shown with their avatar, display name, email address, and a color-coded role badge — so you always know exactly who you're switching to before you click.

### Select Then Switch
Click a user card to select them and preview their info in the footer. Ready to go? Hit **Switch Now**. In a hurry? **Double-click** any card to switch immediately.

### Persistent Switch-Back Bar
While you're viewing the site as another user, a fixed bar stays visible at the bottom of every page — admin and front-end alike — showing who you're currently switched to and a one-click button to jump straight back to your admin account.

### Keyboard Shortcuts
| Shortcut | Action |
|---|---|
| `Alt + S` | Open the Switch User modal |
| `Esc` | Close the modal |
| `Enter` | Select the focused user card |
| `Double-click` | Select and switch immediately |

### Fully Secure
- All requests are verified with WordPress nonces
- Login URLs are encrypted — no passwords are ever transmitted
- Only users with the `manage_options` capability can switch accounts
- Cookies are cleared automatically on logout

### Zero Configuration
Install, activate, and the **Switch User** button appears in your WordPress admin bar. No settings page to configure, no API keys to enter.

---

## Screenshots

| Modal — User Search | Role Filter | Switch Back Bar |
|---|---|---|
| Search by name, email, or username with instant results | Filter the user list by WordPress role | Persistent bar lets you jump back instantly |

---

## Installation

### From WordPress Admin (Recommended)
1. Go to **Plugins → Add New** in your WordPress dashboard.
2. Search for **User Switcher**.
3. Click **Install Now**, then **Activate**.

### Manual Install
1. Download the plugin ZIP file.
2. Go to **Plugins → Add New → Upload Plugin**.
3. Select the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

### Via WP-CLI
```bash
wp plugin install user-switcher --activate
```

---

## How to Use

### Switching to Another User
1. Log in as an administrator.
2. Click **Switch User** in the WordPress admin bar (top of any admin page).
3. The user picker modal opens showing all users — scroll, search, or filter by role.
4. Click a user card to select them. Their avatar and role appear in the footer.
5. Click **Switch Now** (or double-click the card) to switch instantly.

### Switching Back
While switched to another account, a **"Back to [Your Name]"** bar is fixed to the bottom-left corner of every page. Click it to return to your admin account in one click.

### Keyboard Power Users
Press `Alt + S` anywhere in the admin to open the modal without touching the mouse. Use `Esc` to close it.

---

## Frequently Asked Questions

**Who can switch users?**
Only users with the `manage_options` capability (Administrators by default). Regular users cannot see or use the switcher.

**Do I need to know the other user's password?**
No. User Switcher uses a secure, time-limited encrypted URL to log you in. No passwords are stored or transmitted.

**Can I switch to an Administrator account?**
Yes. You can switch to any account on the site, including other administrators.

**Is it safe to use on a live/production site?**
Yes. All actions are nonce-verified and capability-checked. The switch-back cookie expires after 24 hours. We recommend restricting admin access on production sites to trusted IPs as a general best practice.

**What happens if I close my browser while switched?**
The switch cookie expires after 24 hours. You can also manually clear it by clicking the Switch Back button.

**Will it work with my theme or page builder?**
Yes. The switch-back bar and all plugin assets are loaded independently of your theme.

**Can I switch users from the front-end of the site?**
The switch modal is available only in the WordPress admin. The switch-back bar appears on both the admin and front-end while you are in a switched session.

**Is this plugin multisite compatible?**
Multisite support is planned for a future release. Currently the plugin works per-site.

---

## Changelog

### 1.1.0
- Complete UX redesign of the switch modal
- Added scrollable user cards with avatar, email, and role badge
- Added role filter tabs (dynamically loaded from WordPress)
- Added recent users section (last 5 switches, stored per admin)
- Added instant search — no minimum character requirement
- Added single-click select with footer preview, double-click to switch immediately
- Added persistent switch-back bar on admin and front-end pages
- Added `Alt+S` keyboard shortcut to open the modal
- Added toast notifications for switch success and errors
- Removed Select2 dependency — replaced with custom, lightweight picker
- Added current admin user display in modal header
- Security: richer nonce verification on all new AJAX endpoints
- Performance: new `get_roles` and `get_user_info` AJAX endpoints

### 1.0.0
- Initial release

---

## Contributing

Contributions are welcome. Please open an issue to discuss what you would like to change before submitting a pull request.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes
4. Push to the branch and open a pull request

---

## Support

- **WordPress.org Support Forum**: [wordpress.org/support/plugin/user-switcher](https://wordpress.org/support/plugin/user-switcher/)
- **Video Tutorial**: [Watch on YouTube](https://youtu.be/f72goWFsTq4)
- **Author**: [Mahbub](https://profiles.wordpress.org/mahbubmr500/)

---

## License

User Switcher is open-source software licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
