<?php
/**
 * Master List of Australian Suburbs (Validation White List)
 * 
 * Purpose: Prevent typos/spam ("Sydny") from polluting the public search dropdown.
 * Usage: Checked during Sitter Profile Save.
 * 
 * - If User Suburb is IN this list -> Auto-Approved (Taxonomy Term Created).
 * - If User Suburb is NOT in this list -> Saved as Text Only (Quarantined).
 */

if (!defined('ABSPATH')) exit;

if (!function_exists('antigravity_v200_get_master_suburbs_list')) {
function antigravity_v200_get_master_suburbs_list() {
    // A comprehensive list of common suburbs. 
    // NOTE: This is a large representative list. 
    // To be truly comprehensive (15,000+), you can paste the full Australia Post CSV data here.
    
    return [
        // NSW
        'Sydney', 'Parramatta', 'Blacktown', 'Penrith', 'Richmond', 'Windsor', 'Liverpool', 'Campbelltown', 'Camden',
        'Cronulla', 'Sutherland', 'Manly', 'Mosman', 'Bondi', 'Coogee', 'Maroubra', 'Randwick', 'Newtown', 'Balmain',
        'Chatswood', 'Hornsby', 'Ryde', 'Epping', 'Strathfield', 'Burwood', 'Ashfield', 'Bankstown', 'Hurstville',
        'Newcastle', 'Maitland', 'Cessnock', 'Singleton', 'Muswellbrook', 'Port Macquarie', 'Coffs Harbour',
        'Wollongong', 'Shellharbour', 'Kiama', 'Nowra', 'Bomaderry', 'Ulladulla', 'Batemans Bay',
        'Central Coast', 'Gosford', 'Wyong', 'The Entrance', 'Terrigal', 'Woy Woy',
        'Wagga Wagga', 'Albury', 'Griffith', 'Leeton', 'Tamworth', 'Armidale', 'Inverell', 'Moree',
        'Dubbo', 'Orange', 'Bathurst', 'Mudgee', 'Parkes', 'Forbes', 'Cowra', 'Lithgow', 'Goulburn',
        'Lismore', 'Ballina', 'Byron Bay', 'Tweed Heads', 'Casino', 'Grafton', 'Yamba',
        'Broken Hill', 'Snowy Mountains', 'Cooma', 'Jindabyne',
        
        // VIC
        'Melbourne', 'Geelong', 'Ballarat', 'Bendigo', 'Shepparton', 'Mildura', 'Warrnambool', 'Traralgon', 'Morwell',
        'Frankston', 'Dandenong', 'Pakenham', 'Berwick', 'Cranbourne', 'Narre Warren', 'Springvale', 'Noble Park',
        'Clayton', 'Glen Waverley', 'Mount Waverley', 'Box Hill', 'Doncaster', 'Ringwood', 'Croydon', 'Lilydale',
        'Epping', 'Preston', 'Reservoir', 'Coburg', 'Brunswick', 'Essendon', 'Moonee Ponds', 'Footscray', 'Sunshine',
        'Werribee', 'Hoppers Crossing', 'Point Cook', 'Alton', 'Williamstown', 'St Kilda', 'Prahran', 'South Yarra',
        'Richmond', 'Hawthorn', 'Camberwell', 'Kew', 'Toorak', 'Malvern', 'Brighton', 'Sandringham', 'Mentone',
        'Wodonga', 'Wangaratta', 'Echuca', 'Swan Hill', 'Horsham', 'Sale', 'Bairnsdale', 'Wonthaggi', 'Cowes',
        
        // QLD
        'Brisbane', 'Ipswich', 'Logan City', 'Redcliffe', 'Caboolture', 'Strathpine', 'Cleveland', 'Capalaba',
        'Gold Coast', 'Surfers Paradise', 'Southport', 'Burleigh Heads', 'Coolangatta', 'Robina', 'Nerang',
        'Sunshine Coast', 'Maroochydore', 'Caloundra', 'Noosa Heads', 'Nambour', 'Gympie',
        'Toowoomba', 'Warwick', 'Dalby', 'Stanthorpe', 'Goondiwindi', 'Gatton',
        'Bundaberg', 'Hervey Bay', 'Maryborough', 'Gladstone', 'Rockhampton', 'Yeppoon', 'Emerald',
        'Mackay', 'Townsville', 'Ayr', 'Charters Towers', 'Bowen', 'Cairns', 'Port Douglas', 'Atherton', 'Mareeba',
        'Mount Isa',
        
        // WA
        'Perth', 'Fremantle', 'Joondalup', 'Rockingham', 'Mandurah', 'Armadale', 'Midland', 'Cannington',
        'Bunbury', 'Busselton', 'Margaret River', 'Albany', 'Geraldton', 'Kalgoorlie', 'Broome', 'Port Hedland', 'Karratha',
        'Esperance', 'Exmouth', 'Carnarvon',
        
        // SA
        'Adelaide', 'Glenelg', 'Brighton', 'Marion', 'Morphett Vale', 'Noarlunga', 'Salisbury', 'Elizabeth', 'Gawler',
        'Mount Barker', 'Victor Harbor', 'Murray Bridge', 'Mount Gambier', 'Whyalla', 'Port Lincoln', 'Port Pirie', 'Port Augusta',
        'Renmark', 'Berri', 'Loxton',
        
        // TAS
        'Hobart', 'Glenorchy', 'Kingston', 'Sorell', 'Launceston', 'Devonport', 'Burnie', 'Ulverstone', 'Wynyard',
        
        // NT / ACT
        'Darwin', 'Palmerston', 'Katherine', 'Alice Springs',
        'Canberra', 'Queanbeyan', 'Gungahlin', 'Belconnen', 'Woden', 'Tuggeranong'
    ];
}
}

/**
 * Validate Suburb
 * Returns TRUE if suburb is in the Master List (Case Insensitive).
 */
if (!function_exists('antigravity_v200_is_valid_suburb')) {
function antigravity_v200_is_valid_suburb($suburb_input) {
    if (empty($suburb_input)) return false;
    
    $master_list = antigravity_v200_get_master_suburbs_list();
    
    foreach ($master_list as $valid) {
        if (strcasecmp($valid, trim($suburb_input)) === 0) {
            return $valid; // Return the properly cased version
        }
    }
    
    return false; // Not found
}
}


