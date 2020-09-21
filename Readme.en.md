# View Statistics for TYPO3 Frontend Users

This extension inserts statistics records on each page. It doesn't use any cookies!

## Features

* Tracks page views and extension records
* Configure who is tracked
    * all visitors (both logged-in and non-logged-in frontend users)
    * only frontend users who are logged in
    * only visitors who have not logged in
* Optional: Track the ID of the frontend user
* Optional: Track IP address
* Optional: Track login duration of frontend users
* Easy to configure using the extension manager and Typoscript
* Backend module with:
    *   Overview of all tracking information
    *   Listing by page
    *   Listing by user
    *   Listing by object (downloads, news, shop products, portfolios and more)
    *   CSV export of tracking records
    *   User permissions: Admin users see all tracking data. Editors see only data on selected pages.
*   Tracking of pages and objects such as:
    *   Displaying News (EXT:news)
    *   Downloading Files (EXT:downloadmanager)
    *   Products (EXT:shop)
    *   Realty/Properties (EXT:openimmo)
    *   Configure your own objects using TypoScript

> **Warning:**
>
> This extension doesn't track anything if you log in as a backend user and access the frontend simultaneously with
> the same domain name. In this case open another browser as a frontend user in order to trigger tracking.
> Even an incognito window of the same browser might prevent tracking.

### Links

*   [TYPO3 View-Statistics Product details][link-typo3-view-statistics-product-details]
*   [TYPO3 View-Statistics Documentation][link-typo3-view-statistics-documentation]

[link-typo3-view-statistics-product-details]: https://www.coding.ms/typo3-extensions/typo3-view-statistics "TYPO3 View-Statistics Product details"
[link-typo3-view-statistics-documentation]: https://www.coding.ms/documentation/typo3-view-statistics "TYPO3 View-Statistics Documentation"
