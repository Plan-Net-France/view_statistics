# Configure the View Statistics extension

## General configuration

The following global settings are made in the extension settings in extension manager:

*   **Who should be tracked?**
    This setting defines tracking behavior. Possible options are:
    *   **nonLoggedInOnly**
        Only page views from users who are not logged in are tracked.
    *   **loggedInOnly**
        Only page views from loggedin users are tracked.
    *   **all**
        All page views are tracked, regardless of whether the user is logged in or not.
*   **Track frontend user ID?**
    If this is checked, each tracking data record saves the id of the logged-in frontend user who triggered it.
*   **Track IP address?**
    If this is checked, the requesting IP is saved in each tracking data record.
*   **Track user agent?**
    If this is checked, the user agent (eg. browser) is saved.
*   **Track login duration?**
    If this is checked, how long the frontend user is logged in.
