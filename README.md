# zbgis-visualizer
This tool allows visualization and pulls information of all "E-ground plots" for a given name and city in Slovak Cadastre of Real Estate (zbgis.skgeodesy.sk)

## Requirements
- Server with PHP support
- cURL library installed

## How to use it
1. create empty file cookies.txt
2. run search.php at your domain
3. visit [https://zbgis.skgeodesy.sk/mkzbgis/sk/kataster](https://zbgis.skgeodesy.sk/mapka/sk/kataster), click somewhere in the map and pass the captcha test
4. copy the *.ESKN_RECAPTCHA* cookie from the developer console (in Google Chrome, press F12 key, select Application tab, Cookie files, zbgis.skgeodesy.sk), select&copy Cookie Value as shown below:
   
![eskn](https://github.com/palsoft333/zbgis-visualizer/assets/13525347/06d859e0-6767-4fbe-b243-0621473478e4)

6. Insert copied cookie to the first row at search.php
7. *No. of cadastral unit* (Č. katastrálneho územia) can be obtained at https://zbgis.skgeodesy.sk/mkzbgis/sk/kataster by searching the city and clicking on it, as shown below:
   
![ku](https://github.com/palsoft333/zbgis-visualizer/assets/13525347/9e2b9e06-e9eb-4487-9c22-57a8bd4fd71b)

9. Finaly, type the *Surname* (Priezvisko) and *First name* (Meno) without any diacritics and hit *Hľadaj*

## What information I could get?
- first you will be shown a list of owners that matches the search criteria
- click on a desired land owner to pull all of his ground plots and visualize them on the map with all of the shares calculated:
  
![map](https://github.com/palsoft333/zbgis-visualizer/assets/13525347/d6995675-46ca-4c6a-a65b-e2d5818246a3)

If you like it:<br>
<a href="https://www.buymeacoffee.com/palsoft"><img src="https://img.buymeacoffee.com/button-api/?text=Buy me a beer&emoji=🍺&slug=palsoft&button_colour=0091e6&font_colour=ffffff&font_family=Poppins&outline_colour=ffffff&coffee_colour=FFDD00"></a>
