Check out covid map
# API 
## Add case
URL : http://coronavirustrackermap.epizy.com/locations_model.php?add_location&description=' + description + '&lat=' + lat + '&lng=' + lng

Input: 
* description: str
  Name of the place for the given Marker (This is fetched from gmap api)
* lat: float
  Latitude of the Marker (This is fetched from gmap api)
* lng: float
  Longitude of the Marker (This is fetched from gmap api)
  
Response:
* String with results inserted successfully

## Get list of all confirmed cases
URL: http://coronavirustrackermap.epizy.com/locations_model.php?get_confirmed_locations

Input:
* None

Return:
Array of Array
- Each array represents row
- Format: [id, lat, lng, descripiton, confirmed_date]


## Confirm a reported case
Automated script tht confirms any reported case
URL: http://coronavirustrackermap.epizy.com/locations_model.php?automate_confirm_location
Input:
* None

Return:
* String with results updated successfully


Google Map API:
* Places service
* Places SearchBox service




