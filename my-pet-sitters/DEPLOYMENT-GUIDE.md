# Deploying My Pet Sitters

You have two options to deploy your code. **Option 1** is the easiest and recommended method.

## Option 1: The "Plugin" Method (Recommended)
This method installs everything as a single plugin. When you activate it, it will automatically create all the pages (Login, Dashboard, Landing Page, etc.) and set up your menu.

### Steps:
1.  **Download Code**: Locate the `refactored` folder on your computer.
2.  **Zip It**: Rename the `refactored` folder to `my-pet-sitters`. Right-click it and select "Send to > Compressed (zipped) folder". You should now have `my-pet-sitters.zip`.
3.  **Upload to WordPress**:
    *   Log in to your live site (`/wp-admin/`).
    *   Go to **Plugins > Add New**.
    *   Click **Upload Plugin** (top left).
    *   Select your `my-pet-sitters.zip` file and click **Install Now**.
4.  **Activate**: Click **Activate Plugin**.
5.  **Done!**
    *   The plugin will automatically run the installer.
    *   Check your website homepage—the Menu should have "Become a Sitter", "Join", etc.
    *   Visit `/become-a-sitter/` to confirm the landing page exists.
    *   Visit `/edit-profile/` to confirm the profile edit form page exists.

### Updating the Plugin (If you already have it installed)
If you have already installed a previous version of "MPS Core Loader" or "My Pet Sitters":
1.  **Deactivate**: Go to **Plugins**, find "MPS Core Loader", and click **Deactivate**.
2.  **Delete**: Click **Delete** to remove the old files (Required to avoid "Folder already exists" errors).
    *   *Note: This deletes the plugin files, but your Sitters and Users in the database are SAFE.*
3.  **Use Option 1**: Follow the "Upload to WordPress" steps above to install the new version.

---

## Option 2: The Manual Method (FTP)
Use this only if Option 1 fails or you prefer manual control.

1.  **Connect via FTP**: Use FileZilla or your host's File Manager.
2.  **Navigate**: Go to `/wp-content/plugins/`.
3.  **Upload**: Upload the entire `refactored` folder here. Rename it to `my-pet-sitters`.
4.  **Activate**: Go to WP Admin > Plugins and activate "MPS Core Loader".
5.  **Run Installer**: If pages are missing, you may need to de-activate and re-activate the plugin to trigger the installer script.

## Post-Deployment Checklist
1.  **Permalinks**: Go to **Settings > Permalinks** and just click "Save Changes". This fixes any "404 Not Found" errors on the new pages.
2.  **Email**: Install the **WP Mail SMTP** plugin (free) to ensure your system emails (Notifications, Password Resets) actually get delivered to inboxes and not spam folders.
3.  **Test**:
    *   Register a new test user at `/join/`.
    *   Try to Book a Sitter.
    *   Try to Send a Message.
