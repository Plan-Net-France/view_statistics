# Configure the View Statistics extension

## General configuration

The following global settings are made in the extension settings in extension manager:

*   **Track user data from logged in user?**
    If this is checked, each tracking data record saves the name of the loggedin frontend user who triggered it. In addition, the length of time that the frontend user is loggedin is saved.
*   **Track ip address?**
    If this is checked, the requesting IP is saved in each tracking data record.
*   **Who should be tracked?**
    This setting defines tracking behavior. Possible options are:
    *   **nonLoggedInOnly**
        Only page views from users who are not logged in are tracked.
    *   **loggedInOnly**
        Only page views from loggedin users are tracked.
    *   **all**
        All page views are tracked, regardless of whether the user is logged in or not.
