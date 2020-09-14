# View Statistics for TYPO3 Frontend-Users

This extension inserts statistic records on each page view. This extension doesn't use any cookies!

**Features:**

*	Configure who should be tracked (configurable in Extensionmanager):
    *   only non logged in visitors
    *   only logged in Frontend-User
    *   logged in and non logged in Frontend-User
*   Configure if the user data of logged in user should be tracked (configurable in Extensionmanager). If you don't track that user data, the tracking records behave like non logged in user.
*   Optionally track IP-Address (configurable in Extensionmanager)
*   Tracking of login times for logged in Frontend user
*	Backend-Modul with:
    *   Overview about all trackings
    *   Listing by page
    *   Listing by user
    *   Listing by object (downloads, news, shop products, portfolios and more)
    *   CSV export for tracking records
    *   User restriction: Admin user see the whole tracking data. Editor user only the data from current selected page.
*   Tracking for pages and objects like:
    *   Displaying News (EXT:news)
    *   Downloading Files (EXT:downloadmanager with type restricted)
    *   Products (EXT:shop)
    *   Realty/Properties (EXT:openimmo)
    *   Configure your own object by TypoScript

>	**Attention:**
>
>	This extension doesn't log when you're logged in with a Backend-User the same time and call the Frontend by the same domain name. In this case use another Browser for your Frontend-User, in order to trigger tracking! An incognito Browser window might prevent this tracking as well.



### Links

*   [TYPO3 View-Statistics Product details][link-typo3-view-statistics-product-details]
*   [TYPO3 View-Statistics Documentation][link-typo3-view-statistics-documentation]



[link-typo3-view-statistics-product-details]: https://www.coding.ms/products/typo3-view-statistics/ "TYPO3 View-Statistics Product details"
[link-typo3-view-statistics-documentation]: https://www.coding.ms/documentation/typo3-view-statistics/ "TYPO3 View-Statistics Documentation"
