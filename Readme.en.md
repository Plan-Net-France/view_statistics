# View Statistics for TYPO3 Frontend Users

This extension inserts statistics records on each page. It doesn't use any cookies!

**Features:**

*	Configure who is tracked (configurable in Extensionmanager):
    *   only visitors who have not logged in
    *   only frontend users who are logged in
    *   both loggedin and non-loggedin frontend users
*   Configure if user data about loggedin users is tracked (configurable in Extensionmanager). If track user data is not set, the tracking records are the same as for non-loggedin users.
*   Optionally track IP Address (configurable in Extensionmanager)
*   Track login duration for loggedin frontend users
*	Backend Module with:
    *   Overview of all tracking information
    *   Listing by page
    *   Listing by user
    *   Listing by object (downloads, news, shop products, portfolios and more)
    *   CSV export of tracking records
    *   User permissions: Admin users see all tracking data. Editors see only data on selected page.
*   Tracking of pages and objects such as:
    *   Displaying News (EXT:news)
    *   Downloading Files (EXT:downloadmanager with type restrictions)
    *   Products (EXT:shop)
    *   Realty/Properties (EXT:openimmo)
    *   Configure your own objects using TypoScript

>	**Warning:**
>
>	This extension doesn't register if you log in as a backend user and access the frontend simultaneously with the same domain name. In this case open another browser as a frontend user in order to trigger tracking. An incognito browser window might prevent tracking as well.



### Links

*   [TYPO3 View-Statistics Product details][link-typo3-view-statistics-product-details]
*   [TYPO3 View-Statistics Documentation][link-typo3-view-statistics-documentation]



[link-typo3-view-statistics-product-details]: https://www.coding.ms/products/typo3-view-statistics/ "TYPO3 View-Statistics Product details"
[link-typo3-view-statistics-documentation]: https://www.coding.ms/documentation/typo3-view-statistics/ "TYPO3 View-Statistics Documentation"
