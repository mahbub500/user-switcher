=== User Switcher ===
Contributors: mahbubmr500
Tags: user switcher, fast user switching, users , woocommerce
Requires at least: 5.0
Tested up to: 6.7.1
Requires PHP: 7.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Switch between any WordPress user account in one click — no password needed. Built for developers, agencies, and site administrators.

== Description ==

**User Switcher** lets administrators instantly switch to any WordPress user account without logging out or knowing their password. Search, filter, and switch in seconds — then jump back to your admin account with one click.

Whether you are debugging a permissions issue, testing a new user flow, checking how content looks for a specific role, or managing accounts for a client — User Switcher gives you a faster, cleaner way to do it.

= How It Works =

Click **Switch User** in the WordPress admin bar. A polished modal opens with all your site's users ready to browse. Search by name, email, or username. Filter by role. Click a user to select them, then hit **Switch Now** — or double-click any card to jump straight in. A persistent bar stays at the bottom of every page while you are switched so you can always get back in one click.

= Key Features =

**Smart User Picker**
A beautiful modal with avatar, display name, email, and color-coded role badge for every user. Scroll the full list or search to narrow it down — results appear as you type with no minimum character requirement.

**Role Filter Tabs**
Filter users by WordPress role with one click. See only Editors, only Subscribers, or all users at once.

**Recent Users**
Your last five switched accounts are saved and shown at the top of the modal so you can return to a frequently tested account in one click.

**Select Then Switch**
Click a card to preview the user in the footer, then click Switch Now. Or double-click any card to switch immediately without an extra step.

**Persistent Switch-Back Bar**
While switched to another account, a fixed bar is visible at the bottom-left of every page — admin and front-end alike — showing who you are currently viewing as, with a one-click button to return to your own account.

**Keyboard Shortcuts**
Open the modal with Alt+S from anywhere in the admin. Close it with Esc. Navigate and select cards with the keyboard.

**Zero Configuration**
Install and activate. The Switch User button appears in the admin bar immediately — no settings page, no API keys.

**Fully Secure**
All actions are verified with WordPress nonces. Login URLs are encrypted — no passwords are ever transmitted or stored. Only administrators with the manage_options capability can switch accounts.

= Perfect For =

* **WordPress Developers** — Test your code as different user roles without managing multiple browsers.
* **Agencies** — Quickly access client accounts to troubleshoot issues without resetting passwords.
* **QA Testers** — Move between user types rapidly when running through test cases.
* **Site Administrators** — Verify that new users see the right content and have the right permissions.
* **Theme and Plugin Authors** — Check role-restricted features from inside a single browser session.

== Installation ==

= From WordPress Admin (Recommended) =

1. Go to **Plugins > Add New** in your WordPress dashboard.
2. Search for **User Switcher**.
3. Click **Install Now**, then **Activate**.

= Manual Installation =

1. Download the plugin ZIP file from WordPress.org.
2. Go to **Plugins > Add New > Upload Plugin**.
3. Select the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

= Via WP-CLI =

`wp plugin install user-switcher --activate`

== Frequently Asked Questions ==

= Who can switch users? =

Only users with the `manage_options` capability — Administrators by default. Regular editors, authors, contributors, and subscribers cannot see or use the plugin.

= Do I need to know the other user's password? =

No. User Switcher uses a secure, time-limited encrypted URL to authenticate the switch. No passwords are stored, transmitted, or required.

= Can I switch to another Administrator? =

Yes. You can switch to any user account on the site, including other administrators.

= Is it safe to use on a production site? =

Yes. Every request is nonce-verified and capability-checked. The switch cookie expires after 24 hours. As a general best practice, restrict admin access on live sites to trusted users and IPs.

= What happens if I close my browser while switched? =

The switch cookie expires automatically after 24 hours. You can also clear it manually by clicking the Switch Back button, or it will be cleared the next time you log in normally.

= Can I switch users from the front-end of the site? =

The switch user modal is available only from the WordPress admin area. The switch-back bar appears on both admin and front-end pages while you are in a switched session.

= Does it work with multisite? =

Multisite support is planned for an upcoming release. Currently the plugin works on a per-site basis.

= Will it conflict with other user management plugins? =

User Switcher is lightweight and uses its own namespace. It does not modify user data or override WordPress authentication hooks. Conflicts are unlikely, but if you encounter one please open a support thread.

== Screenshots ==

1. The Switch User modal — search, filter by role, and browse user cards with avatar, name, email, and role badge.
2. Role filter tabs — narrow the user list to a specific WordPress role in one click.
3. Footer preview — select a user and see their details before confirming the switch.
4. Switch-back bar — fixed to the bottom of every page while switched so you can return instantly.
5. Admin bar entry — the Switch User button sits in the WordPress admin bar, always within reach.

== Changelog ==

= 1.1.0 =
* Complete UX redesign of the switch modal
* Added scrollable user cards with avatar, display name, email, and color-coded role badge
* Added role filter tabs loaded dynamically from WordPress roles
* Added recent users section showing the last 5 switched accounts
* Added instant search with no minimum character requirement
* Added single-click select with footer preview panel
* Added double-click on any card to switch immediately
* Added persistent switch-back bar on admin and front-end pages
* Added Alt+S keyboard shortcut to open the modal from anywhere in admin
* Added Esc key support to close the modal
* Added toast notifications for switch success and errors
* Added current admin user display (avatar + name) in modal header
* Removed Select2 library dependency — replaced with a custom, lightweight picker
* New AJAX endpoints: get_roles and get_user_info
* Security: nonce verification on all new AJAX endpoints

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
Major UX update. The switch modal has been completely redesigned with instant search, role filters, recent users, and a persistent switch-back bar. Select2 is no longer used and can be deactivated from any asset optimization plugins.
