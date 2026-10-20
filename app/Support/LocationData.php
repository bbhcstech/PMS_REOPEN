<?php

namespace App\Support;

class LocationData
{
    /**
     * Get states/provinces for a given country.
     */
    public static function getStates(string $country): array
    {
        $data = self::data();
        return isset($data[$country]) ? array_keys($data[$country]) : [];
    }

    /**
     * Get cities for a given country and optional state.
     */
    public static function getCities(string $country, ?string $state = null): array
    {
        $data = self::data();
        if (!isset($data[$country])) {
            return [];
        }

        if ($state && isset($data[$country][$state])) {
            return $data[$country][$state];
        }

        // If no state provided or state not found, return all unique cities of that country
        $allCities = [];
        foreach ($data[$country] as $cities) {
            $allCities = array_merge($allCities, $cities);
        }
        return array_values(array_unique($allCities));
    }

    /**
     * Get all location data mapping: Country => [ State => [ Cities... ] ]
     */
    public static function data(): array
    {
        return [
            'India' => [
                'Andhra Pradesh' => ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Nellore', 'Kurnool', 'Rajahmundry', 'Tirupati', 'Kakinada', 'Kadapa', 'Anantapur'],
                'Arunachal Pradesh' => ['Itanagar', 'Naharlagun', 'Pasighat', 'Tawang', 'Ziro'],
                'Assam' => ['Guwahati', 'Silchar', 'Dibrugarh', 'Jorhat', 'Nagaon', 'Tinsukia', 'Tezpur'],
                'Bihar' => ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Purnia', 'Darbhanga', 'Bihar Sharif', 'Arrah', 'Begusarai'],
                'Chhattisgarh' => ['Raipur', 'Bhilai', 'Bilaspur', 'Korba', 'Durg', 'Rajnandgaon'],
                'Goa' => ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda'],
                'Gujarat' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Gandhinagar', 'Junagadh', 'Anand', 'Navsari'],
                'Haryana' => ['Gurugram', 'Faridabad', 'Panipat', 'Ambala', 'Yamunanagar', 'Rohtak', 'Hisar', 'Karnal', 'Sonipat', 'Panchkula'],
                'Himachal Pradesh' => ['Shimla', 'Dharamshala', 'Mandi', 'Solan', 'Kullu', 'Manali'],
                'Jharkhand' => ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro', 'Deoghar', 'Hazaribagh'],
                'Karnataka' => ['Bengaluru', 'Mysuru', 'Hubballi', 'Mangaluru', 'Belagavi', 'Davanagere', 'Ballari', 'Kalaburagi', 'Shivamogga', 'Tumakuru'],
                'Kerala' => ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam', 'Alappuzha', 'Palakkad', 'Kannur', 'Kottayam'],
                'Madhya Pradesh' => ['Bhopal', 'Indore', 'Jabalpur', 'Gwalior', 'Ujjain', 'Sagar', 'Dewas', 'Satna', 'Ratlam'],
                'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Chhatrapati Sambhajinagar', 'Navi Mumbai', 'Solapur', 'Kolhapur', 'Amravati'],
                'Manipur' => ['Imphal', 'Thoubal', 'Bishnupur', 'Churachandpur'],
                'Meghalaya' => ['Shillong', 'Tura', 'Jowai', 'Nongpoh'],
                'Mizoram' => ['Aizawl', 'Lunglei', 'Champhai', 'Serchhip'],
                'Nagaland' => ['Kohima', 'Dimapur', 'Mokokchung', 'Tuensang'],
                'Odisha' => ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Berhampur', 'Sambalpur', 'Puri', 'Balasore'],
                'Punjab' => ['Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda', 'Mohali', 'Hoshiarpur', 'Pathankot'],
                'Rajasthan' => ['Jaipur', 'Jodhpur', 'Kota', 'Bikaner', 'Ajmer', 'Udaipur', 'Bhilwara', 'Alwar', 'Sikar'],
                'Sikkim' => ['Gangtok', 'Namchi', 'Gyalshing', 'Mangan'],
                'Tamil Nadu' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tirunelveli', 'Tiruppur', 'Erode', 'Vellore', 'Thoothukudi'],
                'Telangana' => ['Hyderabad', 'Warangal', 'Nizamabad', 'Karimnagar', 'Khammam', 'Ramagundam', 'Mahbubnagar'],
                'Tripura' => ['Agartala', 'Dharmanagar', 'Udaipur', 'Kailashahar'],
                'Uttar Pradesh' => ['Lucknow', 'Kanpur', 'Varanasi', 'Agra', 'Noida', 'Greater Noida', 'Ghaziabad', 'Prayagraj', 'Meerut', 'Bareilly', 'Aligarh', 'Moradabad', 'Gorakhpur'],
                'Uttarakhand' => ['Dehradun', 'Haridwar', 'Roorkee', 'Haldwani', 'Rishikesh', 'Nainital'],
                'West Bengal' => ['Kolkata', 'Howrah', 'Durgapur', 'Asansol', 'Siliguri', 'Bardhaman', 'Kharagpur', 'Darjeeling', 'Haldia', 'Malda', 'Baharampur', 'Habra'],
                'Delhi' => ['New Delhi', 'Central Delhi', 'North Delhi', 'South Delhi', 'East Delhi', 'West Delhi', 'Dwarka', 'Rohini', 'Connaught Place'],
                'Chandigarh' => ['Chandigarh'],
                'Jammu and Kashmir' => ['Srinagar', 'Jammu', 'Anantnag', 'Baramulla', 'Udhampur'],
                'Ladakh' => ['Leh', 'Kargil'],
                'Puducherry' => ['Pondicherry', 'Karaikal', 'Mahe', 'Yanam'],
                'Goa' => ['North Goa', 'South Goa', 'Panaji', 'Margao'],
                'Andaman and Nicobar Islands' => ['Port Blair'],
            ],
            'United States' => [
                'California' => ['Los Angeles', 'San Francisco', 'San Diego', 'San Jose', 'Sacramento', 'Fresno', 'Oakland', 'Long Beach', 'Irvine'],
                'New York' => ['New York City', 'Buffalo', 'Rochester', 'Yonkers', 'Syracuse', 'Albany', 'White Plains'],
                'Texas' => ['Houston', 'San Antonio', 'Dallas', 'Austin', 'Fort Worth', 'El Paso', 'Arlington', 'Plano'],
                'Florida' => ['Miami', 'Orlando', 'Tampa', 'Jacksonville', 'St. Petersburg', 'Fort Lauderdale', 'Tallahassee'],
                'Illinois' => ['Chicago', 'Aurora', 'Naperville', 'Joliet', 'Rockford', 'Springfield'],
                'Pennsylvania' => ['Philadelphia', 'Pittsburgh', 'Allentown', 'Erie', 'Reading', 'Scranton'],
                'Ohio' => ['Columbus', 'Cleveland', 'Cincinnati', 'Toledo', 'Akron', 'Dayton'],
                'Georgia' => ['Atlanta', 'Augusta', 'Columbus', 'Macon', 'Savannah', 'Athens'],
                'North Carolina' => ['Charlotte', 'Raleigh', 'Greensboro', 'Durham', 'Winston-Salem', 'Fayetteville'],
                'Michigan' => ['Detroit', 'Grand Rapids', 'Warren', 'Sterling Heights', 'Ann Arbor', 'Lansing'],
                'Washington' => ['Seattle', 'Spokane', 'Tacoma', 'Vancouver', 'Bellevue', 'Everett'],
                'Massachusetts' => ['Boston', 'Worcester', 'Springfield', 'Cambridge', 'Lowell'],
                'Arizona' => ['Phoenix', 'Tucson', 'Mesa', 'Chandler', 'Scottsdale', 'Glendale'],
                'Colorado' => ['Denver', 'Colorado Springs', 'Aurora', 'Fort Collins', 'Lakewood', 'Boulder'],
                'Virginia' => ['Virginia Beach', 'Norfolk', 'Chesapeake', 'Richmond', 'Newport News', 'Alexandria'],
                'New Jersey' => ['Newark', 'Jersey City', 'Paterson', 'Elizabeth', 'Trenton'],
                'Washington, D.C.' => ['Washington'],
            ],
            'United Kingdom' => [
                'England' => ['London', 'Birmingham', 'Manchester', 'Leeds', 'Liverpool', 'Bristol', 'Sheffield', 'Newcastle', 'Nottingham', 'Leicester'],
                'Scotland' => ['Edinburgh', 'Glasgow', 'Aberdeen', 'Dundee', 'Inverness', 'Stirling'],
                'Wales' => ['Cardiff', 'Swansea', 'Newport', 'Wrexham', 'Barry'],
                'Northern Ireland' => ['Belfast', 'Derry', 'Lisburn', 'Newry', 'Bangor'],
            ],
            'Canada' => [
                'Ontario' => ['Toronto', 'Ottawa', 'Mississauga', 'Brampton', 'Hamilton', 'London', 'Markham', 'Vaughan'],
                'Quebec' => ['Montreal', 'Quebec City', 'Laval', 'Gatineau', 'Longueuil', 'Sherbrooke'],
                'British Columbia' => ['Vancouver', 'Surrey', 'Burnaby', 'Richmond', 'Victoria', 'Kelowna'],
                'Alberta' => ['Calgary', 'Edmonton', 'Red Deer', 'Lethbridge', 'St. Albert'],
                'Manitoba' => ['Winnipeg', 'Brandon', 'Steinbach'],
                'Saskatchewan' => ['Saskatoon', 'Regina', 'Prince Albert'],
                'Nova Scotia' => ['Halifax', 'Sydney', 'Dartmouth'],
            ],
            'Australia' => [
                'New South Wales' => ['Sydney', 'Newcastle', 'Central Coast', 'Wollongong', 'Maitland'],
                'Victoria' => ['Melbourne', 'Geelong', 'Ballarat', 'Bendigo', 'Shepparton'],
                'Queensland' => ['Brisbane', 'Gold Coast', 'Sunshine Coast', 'Townsville', 'Cairns', 'Toowoomba'],
                'Western Australia' => ['Perth', 'Mandurah', 'Bunbury', 'Geraldton', 'Albany'],
                'South Australia' => ['Adelaide', 'Mount Gambier', 'Whyalla', 'Murray Bridge'],
                'Tasmania' => ['Hobart', 'Launceston', 'Devonport', 'Burnie'],
                'Australian Capital Territory' => ['Canberra'],
            ],
            'Germany' => [
                'Bavaria' => ['Munich', 'Nuremberg', 'Augsburg', 'Regensburg', 'Ingolstadt', 'Würzburg'],
                'Berlin' => ['Berlin'],
                'North Rhine-Westphalia' => ['Cologne', 'Düsseldorf', 'Dortmund', 'Essen', 'Duisburg', 'Bonn', 'Münster'],
                'Baden-Württemberg' => ['Stuttgart', 'Mannheim', 'Karlsruhe', 'Freiburg', 'Heidelberg'],
                'Hesse' => ['Frankfurt', 'Wiesbaden', 'Kassel', 'Darmstadt', 'Offenbach'],
                'Hamburg' => ['Hamburg'],
                'Saxony' => ['Leipzig', 'Dresden', 'Chemnitz'],
            ],
            'France' => [
                'Île-de-France' => ['Paris', 'Boulogne-Billancourt', 'Saint-Denis', 'Argenteuil', 'Montreuil', 'Versailles'],
                'Auvergne-Rhône-Alpes' => ['Lyon', 'Saint-Étienne', 'Grenoble', 'Villeurbanne', 'Clermont-Ferrand'],
                'Provence-Alpes-Côte d\'Azur' => ['Marseille', 'Nice', 'Toulon', 'Aix-en-Provence', 'Avignon', 'Cannes'],
                'Occitanie' => ['Toulouse', 'Montpellier', 'Nîmes', 'Perpignan'],
                'Nouvelle-Aquitaine' => ['Bordeaux', 'Limoges', 'Poitiers', 'Pau', 'La Rochelle'],
            ],
            'United Arab Emirates' => [
                'Dubai' => ['Dubai City', 'Deira', 'Jumeirah', 'Al Barsha', 'Downtown Dubai'],
                'Abu Dhabi' => ['Abu Dhabi City', 'Al Ain', 'Madinat Zayed'],
                'Sharjah' => ['Sharjah City', 'Khor Fakkan', 'Kalba'],
                'Ajman' => ['Ajman City', 'Masfout'],
                'Ras Al Khaimah' => ['Ras Al Khaimah City', 'Al Rams'],
                'Fujairah' => ['Fujairah City', 'Dibba Al-Fujairah'],
                'Umm Al Quwain' => ['Umm Al Quwain City'],
            ],
            'Iceland' => [
                'Capital Region' => ['Reykjavík', 'Kópavogur', 'Hafnarfjörður', 'Garðabær', 'Mosfellsbær', 'Seltjarnarnes'],
                'Southern Peninsula' => ['Reykjanesbær', 'Grindavík', 'Sandgerði', 'Garður', 'Vogar'],
                'Western Region' => ['Akranes', 'Borgarnes', 'Stykkishólmur', 'Grundarfjörður', 'Ólafsvík'],
                'Westfjords' => ['Ísafjörður', 'Bolungarvík', 'Patreksfjörður', 'Hólmavík'],
                'Northwestern Region' => ['Sauðárkrókur', 'Blönduós', 'Hvammstangi'],
                'Northeastern Region' => ['Akureyri', 'Húsavík', 'Dalvík', 'Siglufjörður', 'Ólafsfjörður'],
                'Eastern Region' => ['Egilsstaðir', 'Neskaupstaður', 'Seyðisfjörður', 'Fáskrúðsfjörður', 'Reyðarfjörður'],
                'Southern Region' => ['Selfoss', 'Vestmannaeyjar', 'Hveragerði', 'Hvolsvöllur', 'Hella', 'Vík í Mýrdal'],
            ],
            'Bangladesh' => [
                'Dhaka' => ['Dhaka', 'Gazipur', 'Narayanganj', 'Tangail', 'Faridpur', 'Narsingdi'],
                'Chittagong' => ['Chittagong', 'Cox\'s Bazar', 'Comilla', 'Noakhali', 'Feni', 'Brahmanbaria'],
                'Sylhet' => ['Sylhet', 'Moulvibazar', 'Habiganj', 'Sunamganj'],
                'Rajshahi' => ['Rajshahi', 'Bogra', 'Pabna', 'Sirajganj', 'Naogaon'],
                'Khulna' => ['Khulna', 'Jessore', 'Kushtia', 'Satkhira'],
                'Barisal' => ['Barisal', 'Patuakhali', 'Bhola', 'Pirojpur'],
                'Rangpur' => ['Rangpur', 'Dinajpur', 'Saidpur', 'Kurigram'],
                'Mymensingh' => ['Mymensingh', 'Jamalpur', 'Netrokona', 'Sherpur'],
            ],
            'Pakistan' => [
                'Punjab' => ['Lahore', 'Faisalabad', 'Rawalpindi', 'Multan', 'Gujranwala', 'Sialkot', 'Bahawalpur', 'Sargodha'],
                'Sindh' => ['Karachi', 'Hyderabad', 'Sukkur', 'Larkana', 'Nawabshah', 'Mirpur Khas'],
                'Khyber Pakhtunkhwa' => ['Peshawar', 'Mardan', 'Abbottabad', 'Swat', 'Dera Ismail Khan'],
                'Balochistan' => ['Quetta', 'Gwadar', 'Turbat', 'Khuzdar', 'Chaman'],
                'Islamabad Capital Territory' => ['Islamabad'],
            ],
            'Singapore' => [
                'Central Region' => ['Downtown Core', 'Marina Bay', 'Orchard', 'Bukit Merah', 'Queenstown'],
                'East Region' => ['Tampines', 'Bedok', 'Pasir Ris', 'Changi'],
                'North Region' => ['Woodlands', 'Yishun', 'Sembawang'],
                'North-East Region' => ['Hougang', 'Sengkang', 'Punggol', 'Ang Mo Kio', 'Serangoon'],
                'West Region' => ['Jurong East', 'Jurong West', 'Bukit Batok', 'Clementi', 'Choa Chu Kang'],
            ],
            'Malaysia' => [
                'Kuala Lumpur' => ['Kuala Lumpur'],
                'Selangor' => ['Shah Alam', 'Petaling Jaya', 'Subang Jaya', 'Klang', 'Ampang Jaya', 'Kajang'],
                'Johor' => ['Johor Bahru', 'Iskandar Puteri', 'Batu Pahat', 'Muar', 'Kluang'],
                'Penang' => ['George Town', 'Butterworth', 'Bukit Mertajam'],
                'Perak' => ['Ipoh', 'Taiping', 'Teluk Intan'],
                'Sabah' => ['Kota Kinabalu', 'Sandakan', 'Tawau'],
                'Sarawak' => ['Kuching', 'Miri', 'Sibu', 'Bintulu'],
            ],
            'Saudi Arabia' => [
                'Riyadh' => ['Riyadh', 'Al Kharj', 'Diriyah'],
                'Makkah' => ['Jeddah', 'Mecca', 'Taif'],
                'Eastern Province' => ['Dammam', 'Khobar', 'Dhahran', 'Jubail', 'Al Ahsa'],
                'Madinah' => ['Medina', 'Yanbu'],
                'Asir' => ['Abha', 'Khamis Mushait'],
            ],
            'South Africa' => [
                'Gauteng' => ['Johannesburg', 'Pretoria', 'Soweto', 'Sandton', 'Centurion'],
                'Western Cape' => ['Cape Town', 'Stellenbosch', 'George', 'Paarl'],
                'KwaZulu-Natal' => ['Durban', 'Pietermaritzburg', 'Pinetown', 'Newcastle'],
                'Eastern Cape' => ['Gqeberha (Port Elizabeth)', 'East London', 'Mthatha'],
            ],
            'Netherlands' => [
                'North Holland' => ['Amsterdam', 'Haarlem', 'Zaanstad', 'Alkmaar', 'Hilversum'],
                'South Holland' => ['Rotterdam', 'The Hague', 'Leiden', 'Delft', 'Dordrecht'],
                'Utrecht' => ['Utrecht', 'Amersfoort', 'Veenendaal'],
                'North Brabant' => ['Eindhoven', 'Tilburg', 'Breda', '\'s-Hertogenbosch'],
            ],
            'Spain' => [
                'Madrid' => ['Madrid', 'Móstoles', 'Alcalá de Henares', 'Fuenlabrada', 'Leganés'],
                'Catalonia' => ['Barcelona', 'L\'Hospitalet de Llobregat', 'Badalona', 'Terrassa', 'Sabadell'],
                'Andalusia' => ['Seville', 'Málaga', 'Córdoba', 'Granada', 'Jerez de la Frontera', 'Almería'],
                'Valencia' => ['Valencia', 'Alicante', 'Elche', 'Castellón de la Plana'],
            ],
            'Italy' => [
                'Lombardy' => ['Milan', 'Brescia', 'Monza', 'Bergamo', 'Como'],
                'Lazio' => ['Rome', 'Latina', 'Guidonia Montecelio', 'Fiumicino'],
                'Campania' => ['Naples', 'Salerno', 'Giugliano in Campania', 'Caserta'],
                'Veneto' => ['Venice', 'Verona', 'Padua', 'Vicenza', 'Treviso'],
                'Piedmont' => ['Turin', 'Novara', 'Alessandria', 'Asti'],
            ],
            'Switzerland' => [
                'Zurich' => ['Zurich', 'Winterthur', 'Uster'],
                'Geneva' => ['Geneva', 'Vernier', 'Lancy'],
                'Bern' => ['Bern', 'Thun', 'Biel/Bienne'],
                'Vaud' => ['Lausanne', 'Yverdon-les-Bains', 'Montreux'],
                'Basel-Stadt' => ['Basel', 'Riehen'],
            ],
            'Japan' => [
                'Tokyo' => ['Tokyo', 'Shinjuku', 'Shibuya', 'Hachioji', 'Machida'],
                'Osaka' => ['Osaka', 'Sakai', 'Higashiosaka', 'Hirakata'],
                'Kanagawa' => ['Yokohama', 'Kawasaki', 'Sagamihara', 'Fujisawa'],
                'Aichi' => ['Nagoya', 'Toyota', 'Okazaki', 'Ichinomiya'],
                'Kyoto' => ['Kyoto', 'Uji', 'Kameoka'],
            ],
            'China' => [
                'Beijing' => ['Beijing'],
                'Shanghai' => ['Shanghai'],
                'Guangdong' => ['Guangzhou', 'Shenzhen', 'Dongguan', 'Foshan', 'Zhongshan'],
                'Zhejiang' => ['Hangzhou', 'Ningbo', 'Wenzhou', 'Jiaxing'],
                'Jiangsu' => ['Nanjing', 'Suzhou', 'Wuxi', 'Changzhou'],
            ],
            'Brazil' => [
                'São Paulo' => ['São Paulo', 'Guarulhos', 'Campinas', 'São Bernardo do Campo', 'Santo André'],
                'Rio de Janeiro' => ['Rio de Janeiro', 'São Gonçalo', 'Duque de Caxias', 'Nova Iguaçu', 'Niterói'],
                'Minas Gerais' => ['Belo Horizonte', 'Uberlândia', 'Contagem', 'Juiz de Fora'],
                'Federal District' => ['Brasília'],
            ],
            'Mexico' => [
                'Mexico City' => ['Mexico City'],
                'Jalisco' => ['Guadalajara', 'Zapopan', 'Tlaquepaque', 'Puerto Vallarta'],
                'Nuevo León' => ['Monterrey', 'Guadalupe', 'San Nicolás de los Garza', 'Apodaca'],
                'State of Mexico' => ['Ecatepec', 'Nezahualcóyotl', 'Toluca', 'Naucalpan'],
            ],
            'New Zealand' => [
                'Auckland' => ['Auckland'],
                'Canterbury' => ['Christchurch', 'Timaru'],
                'Wellington' => ['Wellington', 'Lower Hutt', 'Porirua'],
                'Waikato' => ['Hamilton', 'Taupo'],
            ],
            'Ireland' => [
                'Leinster' => ['Dublin', 'Dundalk', 'Drogheda', 'Kilkenny', 'Bray'],
                'Munster' => ['Cork', 'Limerick', 'Waterford', 'Ennis', 'Tralee'],
                'Connacht' => ['Galway', 'Sligo', 'Castlebar'],
                'Ulster' => ['Letterkenny', 'Cavan', 'Monaghan'],
            ],
            'Nepal' => [
                'Bagmati' => ['Kathmandu', 'Lalitpur', 'Bhaktapur', 'Bharatpur', 'Hetauda'],
                'Gandaki' => ['Pokhara', 'Baglung', 'Gorkha', 'Vyas'],
                'Koshi' => ['Biratnagar', 'Dharan', 'Itahari', 'Damak'],
                'Lumbini' => ['Butwal', 'Bhairahawa', 'Nepalgunj', 'Tulsipur'],
                'Madhesh' => ['Janakpur', 'Birgunj', 'Kalaiya', 'Jaleshwar'],
            ],
            'Sri Lanka' => [
                'Western' => ['Colombo', 'Dehiwala-Mount Lavinia', 'Moratuwa', 'Negombo', 'Sri Jayawardenepura Kotte'],
                'Central' => ['Kandy', 'Matale', 'Nuwara Eliya', 'Gampola'],
                'Southern' => ['Galle', 'Matara', 'Hambantota'],
                'Northern' => ['Jaffna', 'Kilinochchi', 'Mannar', 'Vavuniya'],
            ],
            'Philippines' => [
                'Metro Manila' => ['Manila', 'Quezon City', 'Makati', 'Taguig', 'Pasig', 'Parañaque'],
                'Cebu' => ['Cebu City', 'Mandaue', 'Lapu-Lapu'],
                'Davao' => ['Davao City', 'Tagum', 'Panabo'],
            ],
            'Indonesia' => [
                'Jakarta' => ['Central Jakarta', 'South Jakarta', 'West Jakarta', 'North Jakarta', 'East Jakarta'],
                'West Java' => ['Bandung', 'Bekasi', 'Depok', 'Bogor'],
                'East Java' => ['Surabaya', 'Malang', 'Kediri'],
                'Bali' => ['Denpasar', 'Kuta', 'Ubud', 'Singaraja'],
            ],
            'Nigeria' => [
                'Lagos' => ['Lagos', 'Ikeja', 'Badagry', 'Ikorodu'],
                'Kano' => ['Kano'],
                'Federal Capital Territory' => ['Abuja'],
                'Rivers' => ['Port Harcourt'],
            ],
            'Kenya' => [
                'Nairobi' => ['Nairobi'],
                'Mombasa' => ['Mombasa'],
                'Kisumu' => ['Kisumu'],
                'Nakuru' => ['Nakuru'],
            ],
            'Egypt' => [
                'Cairo' => ['Cairo', 'New Cairo', 'Helwan'],
                'Giza' => ['Giza', '6th of October City'],
                'Alexandria' => ['Alexandria', 'Borg El Arab'],
            ],
            'Turkey' => [
                'Istanbul' => ['Istanbul'],
                'Ankara' => ['Ankara'],
                'Izmir' => ['Izmir'],
                'Bursa' => ['Bursa'],
                'Antalya' => ['Antalya'],
            ],
            'Argentina' => [
                'Buenos Aires' => ['Buenos Aires', 'La Plata', 'Mar del Plata'],
                'Córdoba' => ['Córdoba', 'Villa Carlos Paz'],
                'Santa Fe' => ['Rosario', 'Santa Fe'],
            ],
        ];
    }
}
