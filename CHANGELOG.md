# (MODX)EvolutionCMS.snippets.ddGetDocuments changelog


## Version 1.0 (2020-03-11)
* \* Attention! (MODX)EvolutionCMS.libraries.ddTools >= 0.30 is required.
* \+ Providers → Customdbtable. Get resources from custom DB table.
* \* Outputters → Yandexmarket:
	* \+ Added the following parameters:
		* \+ `outputterParams->shopData_platform`
		* \+ `outputterParams->shopData_version`
	* \+ Offer `priceOld` will be used if `price` is empty.
	* \+ Offer `pagetitle` will be used if `name` is empty.
	* \+ `0` weight is not displayed because it's invalid.
* \* Extenders → Pagination:
	* \* `extendersParams->pagination->currentPageTpl`: Fixed empty href on default value.
* \* Snippet:
	* \* Empty `extenders` parameter is not used.
* \+ README:
	* \+ Requires.
	* \+ Documentation → Installation.
	* \+ Documentation → Parameters description.
* \+ CHANGELOG.


## Version 0.1 (2015-09-23)
* \+ The first release.


<style>ul{list-style:none;}</style>