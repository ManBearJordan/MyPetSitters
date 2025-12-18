<?php
/**
 * Master List of Australian Locations
 * Used for Datalists and Validation
 */

function antigravity_v200_get_valid_locations() {
    return [
        // CAPITAL CITIES
        'Brisbane', 'Sydney', 'Melbourne', 'Perth', 'Adelaide', 'Canberra', 'Hobart', 'Darwin',
        'Gold Coast',
        
        // NSW REGIONAL
        'Newcastle', 'Wollongong', 'Central Coast', 'Tweed Heads', 'Maitland', 'Wagga Wagga', 
        'Albury', 'Port Macquarie', 'Tamworth', 'Orange', 'Dubbo', 'Queanbeyan', 'Lismore', 
        'Bathurst', 'Coffs Harbour', 'Nowra', 'Bomaderry', 'Goulburn', 'Armidale', 'Broken Hill',
        'Cessnock', 'Grafton', 'Taree', 'Ballina', 'Griffith', 'Byron Bay', 'Forster', 'Tuncurry',
        'Batemans Bay', 'Singleton', 'Mudgee', 'Kempsey', 'Ulladulla', 'Casino', 'Muswellbrook',
        'Inverell', 'Parkes', 'Lithgow', 'Bowral', 'Mittagong', 'Moss Vale', 'Cooma', 'Moree', 
        'Young', 'Cowra', 'Gunnedah', 'Forbes', 'Tumut', 'Narrabri', 'Yass', 'Cootamundra',
        
        // QLD REGIONAL
        'Sunshine Coast', 'Townsville', 'Cairns', 'Toowoomba', 'Mackay', 'Rockhampton', 'Bundaberg',
        'Hervey Bay', 'Gladstone', 'Maryborough', 'Mount Isa', 'Gympie', 'Bongaree', 'Nambour',
        'Yeppoon', 'Warwick', 'Emerald', 'Dalby', 'Bowen', 'Ayr', 'Charters Towers', 'Kingaroy',
        'Roma', 'Goondiwindi', 'Stanthorpe', 'Gatton', 'Beaudesert', 'Airlie Beach', 'Proserpine',
        
        // VIC REGIONAL
        'Geelong', 'Ballarat', 'Bendigo', 'Shepparton', 'Mooroopna', 'Melton', 'Mildura', 
        'Warrnambool', 'Traralgon', 'Wangaratta', 'Ocean Grove', 'Barwon Heads', 'Horsham', 
        'Moe', 'Newborough', 'Morwell', 'Sale', 'Bairnsdale', 'Echuca', 'Wodonga', 'Colac', 
        'Swan Hill', 'Portland', 'Benalla', 'Castlemaine', 'Maryborough', 'Hamilton', 'Ararat',
        'Torquay', 'Jan Juc', 'Seymour', 'Stawell', 'Kyabram', 'Wonthaggi',
        
        // WA REGIONAL
        'Bunbury', 'Busselton', 'Geraldton', 'Albany', 'Kalgoorlie', 'Boulder', 'Karratha', 
        'Broome', 'Port Hedland', 'Esperance', 'Carnarvon', 'Collie', 'Northam', 'Manjimup',
        'Margaret River', 'Dunsborough', 'Exmouth', 'Denmark',
        
        // SA REGIONAL
        'Mount Gambier', 'Whyalla', 'Gawler', 'Murray Bridge', 'Port Lincoln', 'Port Pirie', 
        'Port Augusta', 'Victor Harbor', 'Goolwa', 'Mount Barker', 'Naracoorte', 'Renmark', 
        'Kadina', 'Wallaroo', 'Moonta', 'Tanunda', 'Nuriootpa',
        
        // TAS REGIONAL
        'Launceston', 'Devonport', 'Burnie', 'Ulverstone', 'Kingston', 'New Norfolk', 'George Town',
        'Sorell', 'Wynyard', 'Latrobe', 'Smithton', 'Scottsdale',
        
        // NT REGIONAL
        'Alice Springs', 'Palmerston', 'Katherine', 'Nhulunbuy', 'Tennant Creek',
        
        // OTHERS / COMMUTER TOWNS (Just in case)
        'Sunbury', 'Pakenham', 'Frankston', 'Dandenong', 'Cronulla', 'Parramatta', 'Penrith',
        'Manly', 'Fremantle', 'Joondalup', 'Mandurah', 'Rockingham', 'Ipswich', 'Logan City',
        'Redcliffe',
    ];
}

function antigravity_v200_get_valid_regions() {
    return [
        'NSW' => [
            'Hunter Region', 'Greater Western Sydney', 'Central Coast', 'Mid North Coast', 'Northern Rivers',
            'New England / North West', 'New England', 'North West', 'Central West', 'Southern Highlands', 'South Coast',
            'Riverina', 'Illawarra', 'Wollongong', 'Snowy Mountains', 'Alpine Country', 'Murray', 'Far West'
        ],
        'QLD' => [
            'South East Queensland', 'Brisbane & Surrounds', 'Gold Coast', 'Sunshine Coast', 'Darling Downs',
            'Wide Bay-Burnett', 'Central Queensland', 'Far North Queensland', 'North Queensland',
            'Gulf Country', 'Central West Queensland', 'South West Queensland'
        ],
        'VIC' => [
            'Greater Melbourne', 'Gippsland', 'Barwon South West', 'Hume', 'Loddon Mallee',
            'Central Victoria', 'Yarra Valley & Dandenong Ranges', 'Mornington Peninsula'
        ],
        'SA' => [
            'Adelaide & Surrounds', 'Barossa Valley', 'Clare Valley', 'Eyre Peninsula',
            'Fleurieu Peninsula', 'Kangaroo Island', 'Murraylands', 'Riverland', 'Far North', 'Limestone Coast'
        ],
        'WA' => [
            'Perth & Peel', 'South West', 'Great Southern', 'Wheatbelt', 'Mid West',
            'Gascoyne', 'Pilbara', 'Kimberley'
        ],
        'TAS' => [
            'Hobart & Surrounds', 'Launceston', 'North West Coast', 'Midlands', 'Central Highlands', 'East Coast'
        ],
        'NT' => [
            'Darwin & Surrounds', 'Katherine Region', 'Top End', 'Central Australia', 'Red Centre'
        ],
        'ACT' => [
            'Canberra'
        ]
    ];
}

/**
 * Get Structured Search Options
 * Returns: ['Major Cities' => [...], 'NSW' => [...], ...]
 */
function antigravity_v200_get_search_options() {
    // Top-tier locations the user likely wants to find quickly
    $cities = [
        'Brisbane', 'Sydney', 'Melbourne', 'Perth', 'Adelaide', 'Canberra', 'Hobart', 'Darwin', 
        'Gold Coast', 'Sunshine Coast', 'Newcastle', 'Central Coast', 'Wollongong'
    ];
    
    $regions_raw = antigravity_v200_get_valid_regions();
    $states_map = [
        'NSW' => 'New South Wales',
        'VIC' => 'Victoria',
        'QLD' => 'Queensland',
        'WA'  => 'Western Australia',
        'SA'  => 'South Australia',
        'TAS' => 'Tasmania',
        'ACT' => 'ACT',
        'NT'  => 'Northern Territory'
    ];
    
    $options = [];
    $options['Major Cities'] = $cities;
    
    foreach ($regions_raw as $state_code => $regions_list) {
        $state_label = $states_map[$state_code] ?? $state_code;
        $options["$state_label ($state_code)"] = $regions_list;
    }
    
    return $options;
}

/**
 * Get Suburbs mapped by Region
 * Used for cascading dropdowns in Registration
 */
function antigravity_v200_get_suburbs_by_region() {
    return [
        // NSW
        'Hunter Region' => ['Newcastle', 'Maitland', 'Cessnock', 'Singleton', 'Muswellbrook', 'Port Stephens', 'Lake Macquarie', 'Kurri Kurri'],
        'Greater Western Sydney' => ['Parramatta', 'Penrith', 'Blacktown', 'Campbelltown', 'Liverpool', 'Fairfield', 'Richmond', 'Windsor', 'Camden'],
        'Central Coast' => ['Gosford', 'Wyong', 'Terrigal', 'The Entrance', 'Woy Woy', 'Avoca Beach', 'Erina'],
        'Mid North Coast' => ['Port Macquarie', 'Coffs Harbour', 'Taree', 'Forster', 'Tuncurry', 'Kempsey', 'Wauchope'],
        'Northern Rivers' => ['Lismore', 'Ballina', 'Byron Bay', 'Tweed Heads', 'Casino', 'Kyogle', 'Grafton', 'Yamba'],
        'New England / North West' => ['Tamworth', 'Armidale', 'Moree', 'Gunnedah', 'Narrabri', 'Inverell', 'Glen Innes'],
        'Central West' => ['Orange', 'Bathurst', 'Dubbo', 'Mudgee', 'Parkes', 'Forbes', 'Cowra', 'Lithgow'],
        'Southern Highlands' => ['Bowral', 'Mittagong', 'Moss Vale', 'Goulburn'],
        'South Coast' => ['Wollongong', 'Nowra', 'Bomaderry', 'Ulladulla', 'Batemans Bay', 'Moruya', 'Bega', 'Merimbula'],
        'Riverina' => ['Wagga Wagga', 'Griffith', 'Albury', 'Leeton', 'Cootamundra', 'Narrandera', 'Deniliquin'],
        'Illawarra' => ['Wollongong', 'Shellharbour', 'Kiama', 'Dapto', 'Albion Park'],
        'Snowy Mountains' => ['Cooma', 'Jindabyne', 'Thredbo', 'Perisher Valley'],
        
        // QLD
        'Brisbane & Surrounds' => ['Brisbane', 'Ipswich', 'Logan City', 'Redcliffe', 'Cleveland', 'Strathpine', 'Caboolture'],
        'South East Queensland' => ['Gold Coast', 'Sunshine Coast', 'Toowoomba', 'Gatton', 'Beaudesert'],
        'Gold Coast' => ['Surfers Paradise', 'Southport', 'Burleigh Heads', 'Coolangatta', 'Robina', 'Nerang', 'Coomera'],
        'Sunshine Coast' => ['Maroochydore', 'Caloundra', 'Noosa Heads', 'Nambour', 'Maleny', 'Gympie'],
        'North Queensland' => ['Townsville', 'Ayr', 'Charters Towers', 'Ingham', 'Bowen'],
        'Far North Queensland' => ['Cairns', 'Port Douglas', 'Atherton', 'Mareeba', 'Innisfail'],
        'Central Queensland' => ['Rockhampton', 'Gladstone', 'Yeppoon', 'Emerald', 'Biloela'],
        'Wide Bay-Burnett' => ['Bundaberg', 'Hervey Bay', 'Maryborough', 'Kingaroy'],
        'Darling Downs' => ['Toowoomba', 'Warwick', 'Dalby', 'Stanthorpe', 'Goondiwindi'],
        
        // VIC
        'Greater Melbourne' => ['Melbourne', 'Geelong', 'Frankston', 'Dandenong', 'Pakenham', 'Sunbury', 'Werribee', 'Melton'],
        'Gippsland' => ['Traralgon', 'Moe', 'Morwell', 'Sale', 'Bairnsdale', 'Warragul', 'Leongatha', 'Wonthaggi'],
        'Barwon South West' => ['Warrnambool', 'Colac', 'Portland', 'Hamilton', 'Torquay', 'Lorne'],
        'Hume' => ['Shepparton', 'Wangaratta', 'Wodonga', 'Benalla', 'Seymour', 'Echuca'],
        'Loddon Mallee' => ['Bendigo', 'Mildura', 'Swan Hill', 'Castlemaine', 'Maryborough', 'Kyneton'],
        'Central Victoria' => ['Ballarat', 'Ararat', 'Stawell', 'Daylesford'],
        
        // WA, SA, TAS placeholders for brevity - User can expand
        'Perth & Peel' => ['Perth', 'Fremantle', 'Joondalup', 'Rockingham', 'Mandurah', 'Armadale'],
        'South West' => ['Bunbury', 'Busselton', 'Margaret River', 'Collie', 'Manjimup'],
        'Adelaide & Surrounds' => ['Adelaide', 'Gawler', 'Mount Barker', 'Salisbury', 'Glenelg'],
        'Hobart & Surrounds' => ['Hobart', 'Glenorchy', 'Kingston', 'Sorell', 'New Norfolk'],
        'Canberra' => ['Canberra', 'Queanbeyan', 'Gungahlin', 'Tuggeranong', 'Belconnen', 'Woden'],
        'Darwin & Surrounds' => ['Darwin', 'Palmerston', 'Casuarina']
    ];
}


