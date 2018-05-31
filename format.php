<?php


	class Format {
		
		//
		// -------------------- STATIC VARIABLES --------------------
		// 
		
		// The dropdown choices @ HOME.PHP
		public static $dropdown_choices = array("Counseling Services" , "Fitness Groups" , "Anxiety Resources");

		// Valid 'organization' columns
		public static $valid_org_col = array("organization", "website", "category", "description", "users", "hoursOfOperation", "imageName", "phoneNumber", "email", "location", "geotag");

		// Valid 'subcategory' columns
		public static $valid_sub_col = array("category", "description", "hotlines");

		// Valid category
		public static $valid_cat = array("SEXUAL HEALTH", "MENTAL HEALTH", "PHYSICAL ACTIVITY", "ALCOHOL & OTHER DRUGS", "NUTRITION");

		// Valid users
		public static $valid_users = array("student", "faculty", "both");

		//
		// -------------------- STATIC FUNCTIONS --------------------
		// 

		// Description: Format the whole organization database
		// Parameter(s):
		// 		- $original_path: The path that points to the organization text file
		// 		- $recovery_path: The path that points to the 'recovery' organization text file
		public static function formatOrganizationContent($original_path, $recovery_path) {

			// Parsed JSON text file to be more readable
			$org = self::rawContent($original_path);
			$result = $org;

			// Iterate through all the organization
			for($i = 0; $i < count($org); $i++) {

				// Iterate through an organization's information
				for($j = 0; $j < count(self::$valid_org_col); $j++){

					// For the columns that are not filled out in the database, initialize each one to have and empty string
					// NOTE: Organization Name, Website, and Category are required fields
					// NOTE: users will default to 'Both' if empty
					// NOTE: Default image will be attached to the organization if image is not define
					if(!(array_key_exists(self::$valid_org_col[$j], $org[$i]))) {
						
						// Initialize an empty string - will be used as the value for the selected column
						$value = "";

						// Define the right value for the specified column
						if(self::$valid_org_col[$j] == "organization" || self::$valid_org_col[$j] == "website" || self::$valid_org_col[$j] == "category") {
							$value = "error";
						} elseif (self::$valid_org_col[$j] == "users") {
							$value = "Both";
						} elseif (self::$valid_org_col[$j] == "imageName") {
							$value = "default.png";
						} elseif (self::$valid_org_col[$j] == "phoneNumber" || self::$valid_org_col[$j] == "email" || self::$valid_org_col[$j] == "location" || self::$valid_org_col[$j] == "description" || self::$valid_org_col[$j] == "hoursOfOperation") {
							$value = "Not Available";
						}

						$result[$i][self::$valid_org_col[$j]] = $value;
					}
				}

				// Check if content the value of the current organization is valid, if not return 'FAILED'
				$check = self::checkValidity($result[$i]);
				if (!$check["Bool"]) {

					// Update the 'corrupted' file with the recorvery file
					copy($recovery_path, $original_path);

					// Update the error for the organization page
					$error = "Error: Organization database failed to load! -- " . $check["Error"] . " -- LOCATED IN: " . $check['Name'];
					
					// Open Error Log
					$myfile = fopen("database/ERROR/organization_error.txt", "w") or die("Unable to open file!");

					// Put the error in the 'error' text
					$txt = $error . "\n";
					fwrite($myfile, $txt);

					// Close Error Log
					fclose($myfile);

					echo "ERROR @ Organization <br>";

					// Run the function again with the valid information
					return self::formatOrganizationContent($original_path, $recovery_path);
				}
				
				// Format Description
				$result[$i]["description"] = self::_formatDescription($result[$i]["description"]);

				// Format Hours of Operation
				if ($result[$i]["hoursOfOperation"] !== "Not Available") { 
					$result[$i]["hoursOfOperation"] = self::_formatHOF($result[$i]["hoursOfOperation"]); 
				} else {
					$result[$i]["hoursOfOperation"] = array("Monday" => "Not Available", "Tuesday" => "Not Available", "Wednesday" => "Not Available", "Thursday" => "Not Available", "Friday" => "Not Available", "Saturday" => "Not Available", "Sunday" => "Not Available");
				}

				// Format Image Link
				$result[$i]["imageName"] = self::_formatImage($result[$i]["imageName"]);

				// Format Phone Number
				if ($result[$i]["phoneNumber"] !== "Not Available") { $result[$i]["phoneNumber"] = self::_formatPhoneNumber($result[$i]["phoneNumber"]); }

			}

			// Update recovery files here
			copy($original_path, $recovery_path);

			return $result;
		}	

		// Description: Format the whole subcategory database
		// Parameter(s):
		// 		- $original_path: The path that points to the organization text file
		// 		- $recovery_path: The path that points to the 'recovery' organization text file		
		public static function formatSubcategoryContent($original_path, $recovery_path) {
			
			// Parsed JSON text file to be more readable
			$category = self::rawContent($original_path);
			$result = $category;

			// Iterate through all the category
			for($i = 0; $i < count($category); $i++) {

				// Iterate through an category's information
				for($j = 0; $j < count(self::$valid_sub_col); $j++){

					// For the columns that are not filled out in the database, initialize each one to have and empty string
					// NOTE: Category is a required field
					if(!(array_key_exists(self::$valid_sub_col[$j], $category[$i]))) {
						// Initialize an empty string - will be used as the value for the selected column
						$value = "";

						// Define the right value for the specified column
						if(self::$valid_sub_col[$j] == "category") {
							$value = "error";
						} elseif (self::$valid_sub_col[$j] == "description" || self::$valid_sub_col[$j] == "hotlines") {
							$value = "Not Available";
						}

						$result[$i][self::$valid_sub_col[$j]] = $value;
					}
				}
				
				// Check if content the value of the current organization is valid, if not return 'FAILED'
				$check = self::checkValidity($result[$i]);
				if (!$check["Bool"]) {
					
					// Update the 'corrupted' file with the recorvery file
					copy($recovery_path, $original_path);

					// Update the error for the organization page -- CHANGE CHECK VALIDITY
					$error = "Error: Category database failed to load! -- " . $check["Error"] . " -- LOCATED IN: " . $check['Name'];
					
					// Open Error Log
					$myfile = fopen("database/ERROR/category_error.txt", "w") or die("Unable to open file!");
					
					// Put the error in the 'error' text
					$txt = $error . "\n";
					fwrite($myfile, $txt);

					// Close Error Log
					fclose($myfile);

					echo "ERROR @ Category <br>";

					// Run the function again with the valid information
					return self::formatSubcategoryContent($original_path, $recovery_path);
				}

				// Format Description
				if ($result[$i]["description"] !== "") { $result[$i]["description"] = self::_formatDescription($result[$i]["description"]); };

				// Format Hotlines
				if ($result[$i]["hotlines"] !== "") { $result[$i]["hotlines"] = self::_formatHotlines($result[$i]["hotlines"]); };
			}
			
			// Update recovery files here
			copy($original_path, $recovery_path);

			return $result;
		}

		// Description: Scans the database searching for resources by two categories -- the working group, and who can use the resources
		// Parameter(s):
		// 		- $category: The path that points to the text file
		// 		- $user: The path that points to the text file
		// 		- $category_data: The path that points to the text file
		public static function get_user_resources($category, $user, $category_data){
			$results = array();

				foreach ($category_data as $value){
					if($value["category"] == $category && $value["users"] == $user){
						// echo "value organization: " . $value["organization"] . "--";
						// echo "value users: " . $value["users"] . "--";
						// echo "user: " . $user . "<br>";
						array_push($results, $value);
					}
				}
		return $results;
		}

		//
		// -------------------- HELPER FUNCTIONS --------------------
		// 

		// Description: Read the content of the text file found in the given path and do a rough format
		// Parameter(s):
		// 		- $pathName: The path that points to the text file
		private static function rawContent($pathName) {

			// Get content (Remove the uunnecessary characters)
			$string = substr(str_replace("\n", "", file_get_contents($pathName)), 7, -8);
			
			// Split strings based on their organization
			$content = preg_split("/}(\s*),(\s*){/", $string);

			// Initiate an empty array - will hold the array that will be used to format
			$result = array();

			// Iterate through the organizations		
			for($i = 0; $i < count($content); $i++) {

				// Initiate an empty array
				$temparr = array();
				
				// Split an organization's contents
				$x = preg_split("/\"(\s*),(\s*)\"/", $content[$i]);

				// Iterate through an organization's contents (ie name, description, etc,.)
				for($j = 0; $j < count($x); $j++) {
					// --- Building the KEY ---
					// Remove the first opening quotation mark that encloses the future KEY (if necessary)
					$word = ($j == 0) ? preg_replace("/\"/", "", $x[$j], 1) : $x[$j];

					// Find the closing quotation mark that encloses the future KEY
					$length = strpos($word, "\"");

					// Remove the unnecessary characters between the future KEY and save it
					$key = preg_replace('/\s+/', '', substr($word, 0, $length));

					// --- Building the VALUE ---
					// Remove the first half of the string (the KEY)
					$word = substr($word, (strpos($word, ":") + 1));

					// Remove the first opening quotation mark that encloses the future VALUE
					$word = substr($word, (strpos($word, "\"") + 1));

					// Remove the closing opening quotation mark that encloses the future VALUE (if necessary)
					$value = ($j == (count($x) - 1)) ? strrev(substr(strrev($word), (strpos(strrev($word), "\"")) + 1)) : $word;

					// Remove whitespace in the beginning and in the end of the string
					$value = ltrim($value);
					$value = rtrim($value);

					// Add the KEY and VALUE pair in the temporary array
					$temparr[$key] = $value;

				}

				// Add all the organization's information in array
				$result[$i] = $temparr;
				
			}

			return $result;
		}

		// Description: For each column given for the selected organization, check if they follow the format stated in README
		// Parameter(s):
		// 		- $organization: The array of the selected organization
		private static function checkValidity($organization) {

			$name_value = (array_key_exists('organization', $organization)) ? $organization['organization'] : $organization['category']; 

			// Initiate a tuple: Bool -> defines if HOF is valid, Error -> describes the error (if applicable)
			$result = array("Bool" => true, "Name" => $name_value, "Error" => "n/a");

			// Iterate through the organization's content (name, description, etc.,)
			foreach ($organization as $key => $value) {

				// If the value is Not available, skip it
			 	if($value == "Not Available") { continue; }
			 	
			 	// If the value is an error, return the error
			 	if ($value == "error") {
			 			echo "here<br>";
			 			$result["Bool"] = false;
			 			$result["Error"] = "Invalid Format - " . $key . ": This field is required";
			 			return $result;
			 	}

			 	if ($key == "website") {

			 		// If the website does not follow the URL format, return FALSE
					if (self::_checkWebsite($value) === 0) {
						$result["Bool"] = false;
			 			$result["Error"] = "Invalid Format - website: " . $value;
			 			return $result;
					}

			 	} elseif ($key == "category") {

			 		// If the category is not in the $valid_cat array, return FALSE
			 		if(self::_checkCategory($value) === 0) {
			 			$result["Bool"] = false;
			 			$result["Error"] = "Invalid Format - category: " . $value;
			 			return $result;
			 		}

			 	} elseif ($key == "users") {

			 		// If the user is not in the $valid_users array, return FALSE
			 		if(self::_checkUsers($value) === 0) {
			 			$result["Bool"] = false;
			 			$result["Error"] = "Invalid Format - users: " . $value;
			 			return $result;
			 		}

			 	} elseif ($key == "hoursOfOperation") {

			 		// If the hours of operation does not follow the correct format, return FALSE
			 		$result = self::_checkHOF($value, $name_value);

			 	} elseif ($key == "phoneNumber") {

			 		// Get the actual number characters in the string
			 		$number = substr($value, 2, -2);

			 		// If the phone number contains anything but number or is more than 10, return FALSE
			 		if(self::_checkPhoneNumber($number) == '') {
			 			$result["Bool"] = false;
			 			$result["Error"] = "Invalid Format - phoneNumber: " . $number;
			 			return $result;
			 		}

			 	} elseif ($key == "email") {

			 		// If the email does not follow an email format, return FALSE
			 		if(self::_checkEmail($value) == '') {
			 		 	$result["Bool"] = false;
			 			$result["Error"] = "Invalid Format - email: " . $value;
			 			return $result;			
			 		}

			 	} elseif ($key == "hotlines") {

			 		// If the hotlines does not follow the valid format, return FALSE
			 		if(self::_checkHotlines($value) === 0)  {
			 			$result["Bool"] = false;
			 			$result["Error"] = "Invalid Format - hotlines: " . $value;
			 			return $result;
			 		}
			 	}

			}
			return $result;
		}


		// Description: Return an array that lists which days are in the specific string
		// Parameter(s):
		// 		- $string: The string that holds some or all of the days in the week
		private static function _determineDays($string) {

			// Initalize an empty array - will hold the array that lists which days are in the $string
			$result = array();

			// Determine if the iteration should be skipped
			$isActive = false;

			// Determine which index should be skipped
			$skip = 0;

			// Iterate through the $string
			for ($i = 0; $i < strlen($string); $i++) { 

				// Ensure that it skips iteration on extra letters (if applicable)
				// Example: 'm' does not have extra letter but 'tu' has, so the iteration won't iterate on the extra letter which is 'u' in this case
				if ((($isActive == true) && ($skip === $i))) { continue; };

				// If the current index is either a 't' or and 's' 
				if($string[$i] == "t" || $string[$i] == "s") {

					// Ensures that it captures the whole 'day'
					$selected = $string[$i] . $string[$i + 1];
					
					// Activate the skipping process mentioned above
					$isActive = true;

					// Mark the index that will be skipped				
					$skip = $i + 1;

				} else {

					// The current character
					$selected = $string[$i];

					// Deactivate the skipping process
					$isActive = false;
				}

				// Determine the day based on the abbreviation and add it to the RESULT
				if ($selected == 'm') {
					array_push($result, "Monday");
				} elseif ($selected == 'tu') {
					array_push($result, "Tuesday");
				} elseif ($selected == 'w') {
					array_push($result, "Wednesday");
				} elseif ($selected == 'th') {
					array_push($result, "Thursday");
				} elseif ($selected == 'f') {
					array_push($result, "Friday");
				} elseif ($selected == 'sa') {
					array_push($result, "Saturday");
				} elseif ($selected == 'su') {
					array_push($result, "Sunday");
				}	

			}

			return $result;
		}	

		// Description: Format the organization's 'description' - split the string into an array using '|par|' as the delimeter
		// Parameter(s):
		// 		- $description: The unformatted 'description'
		private static function _formatDescription($description) {
			return explode("|par|", $description);
		}

		// Description: Format the organization's 'hours of operation'
		// Parameter(s):
		// 		- $HOF: The unformatted 'hours of operation'
		private static function _formatHOF($HOF) {

			// Turn the HOF string to array
			$time = (strpos($HOF, ",") == false) ? array($HOF) : preg_split("/,/", $HOF);

			// Initalize the days
			$hoursOfOperation = array("Monday" => "", "Tuesday" => "", "Wednesday" => "", "Thursday" => "", "Friday" => "", "Saturday" => "", "Sunday" => "");

			// Iterate through the semi-formatted HOF
			for ($i = 0; $i < count($time); $i++) {
				
				// Get the actual days in the current string
				$abbr = substr($time[$i], 0, strpos($time[$i], "="));
				$days = self::_determineDays(strtolower($abbr));

				// Get the time range it specified for the current string
				$range = strtoupper(substr($time[$i], strpos($time[$i], "=") + 1));

				// Iterate through the days
				for ($j = 0; $j < count($days); $j++) { 

					// Populate the HOURSOFOPERATION
					if ($range == "ALLDAY") {
						$hoursOfOperation[$days[$j]] = "Open 24 hours";
					} else {
						$hoursOfOperation[$days[$j]] = $range;
					}
				}
			}

			// If any of the keys does not have value, put 'Closed' for their values
			foreach ($hoursOfOperation as $key => $value) {
				if ($value == "") {
					$hoursOfOperation[$key] = "Closed";
				}
			}

			return $hoursOfOperation;

		}

		// Description: Format the organization's 'image path' - add the correct path to the image
		// Parameter(s):
		// 		- $img: The unformatted 'image path'
		private static function _formatImage($img) {
			return "img/org/" . $img;
		}

		// Description: Format the organization's 'phone number'
		// Parameter(s):
		// 		- $num: The unformatted 'phone number'		
		private static function _formatPhoneNumber($num) {

			// Adds parenthesis and hypen
			$num = (strlen($num) > 10) ? substr($num, 2) : $num;
			return "(" . substr($num, 0, 3) . ") " . substr($num, 3, 3) . " - " . substr($num, 6, 4);
		}

		// Description: Format the organization's 'hotlines'
		// Parameter(s):
		// 		- $hotlines: The unformatted 'hotlines'		
		private static function _formatHotlines($hotlines) {
			
			// Create an array that holds all the phone number with their corresponding organization			
			$phoneNumbers = preg_split("/(\s*),(\s*)/", $hotlines);

			// Initialize an empty array - will hold the formatted phone numbers for the corresponding organization
			$result = array();

			// Iterate through the phone numbers
			for ($i = 0; $i < count($phoneNumbers); $i++) { 

				// Split the organization's name and phone number
				$num = explode("=", $phoneNumbers[$i]);

				// Store the information in the $result array - KEY: organization's name, VALUE: organization's phone number
				$result[$num[0]] = self::_formatPhoneNumber($num[1]);
			}

			return $result;
		}			

		// Description: Checks if the URL follow the URL format
		// NOTE: It does not check the whole web if it exists because it will considerably slowdown the website
		// Parameter(s):
		// 		- $url: The URL of the organization
		private static function _checkWebsite($url) {
			return filter_var($url, FILTER_VALIDATE_URL);
		}

		// Description: Checks if the 'category' is a valid category
		// Parameter(s):
		// 		- $selected: The specified category
		private static function _checkCategory($selected) {
			return in_array(strtoupper($selected), self::$valid_cat);
		}

		// Description: Checks if the 'user' is a valid user
		// Parameter(s):
		// 		- $user: The specified user
		private static function _checkUsers($user) {
			return in_array(strtoupper($user), self::$valid_users);
		}

		// Description: Checks if the format given for Hours of Operation is valid
		// Parameter(s):
		// 		- $HOF: Hours of Operation
		// 		- $value: Defines where the error is located
		private static function _checkHOF($HOF, $value) {

			// Initiate a tuple: Bool -> defines if HOF is valid, Error -> describes the error (if applicable)
			$result = array("Bool" => true, "Name" => $value, "Error" => "n/a");

			// Turn the HOF string to array
			$time = (strpos($HOF, ",") == false) ? array($HOF) : preg_split("/,/", $HOF);

			// List of valid days
			$day = array("m", "tu", "w", "th", "f", "sa", "su");

			// Iterate through the semi-formatted HOF 
			for ($i = 0; $i < count($time); $i++) {
				
				// Determine if the iteration should be skipped
				$isActive = false;

				// Determine which index should be skipped
				$skip = 0;

				// Make sure to stop by '=' sign
				$length = strlen(substr($time[$i], 0, strpos($time[$i], "="))) + 1;

				// Iterate through the 'days'
				for ($j = 0; $j < $length; $j++) {

					// Ensure that it skips iteration on extra letters (if applicable) and the last index (because that's the '=' sign)
					// Example: 'm' does not have extra letter but 'tu' has, so the iteration won't iterate on the extra letter which is 'u' in this case
					if (((($isActive == true) && ($skip === $j))) || $j == ($length  - 1)) { continue; };
					
					// If the current index is either a 't' or and 's' 
					if ($time[$i][$j] == "t" || $time[$i][$j] == "s") {
						
						// Ensures that it captures the whole 'day'
						$selected =  $time[$i][$j] . $time[$i][$j + 1];

						// Activate the skipping process mentioned above
						$isActive = true;

						// Mark the index that will be skipped
						$skip = $j + 1;

					} else {

						// The current character
						$selected =  $time[$i][$j];

						// Deactivate the skipping process
						$isActive = false;
					}

					// If any of the strings containts '=', skip it
					if((strlen($selected) == 2 && $selected[1] == "=")) { continue; };

					// Find the day (ie 'm', 'th', etc.,) and remove it from DAYS array - to prevent repetition of days
					if (($key = array_search($selected, $day)) !== false) {
					    unset($day[$key]);
					} else {
						// Return FALSE if there is repetition OR the day in invalid
						$result["Bool"] = false;
			 			$result["Error"] = "Invalid Format - hoursOfOperation: " . $HOF;
			 			return $result;
					}
				}

				// Gets the time range (ie 08:00AM-12:00PM, allday, etc.,)
				$range = strtoupper(substr($time[$i], strpos($time[$i], "=") + 1));

				// Returns FALSE if the value does not follow the correct time format
				if ($range != "ALLDAY" && (preg_match("#([01]?[0-9]|2[0-3]):[0-5][0-9](AM|PM)\-([01]?[0-9]|2[0-3]):[0-5][0-9](AM|PM)$#", $range) == '')) {
					$result["Bool"] = false;
		 			$result["Error"] = "Invalid Format - hoursOfOperation: " . $HOF;
		 			return $result;
			 	}

			}

			return $result;
		}

		// Description: Checks if the phone number given is valid
		// Parameter(s):
		// 		- $number: The phone number
		private static function _checkPhoneNumber($number) {
			return ctype_digit($number) && (strlen($number) == 10);
		}

		// Description: Checks if the email follows the email format
		// Parameter(s):
		// 		- $email: The email
		private static function _checkEmail($email) {
			return filter_var($email, FILTER_VALIDATE_EMAIL);
		}

		// Checks if all the hotlines given are formatted correctly
		// Parameter(s):
		// 		- $hotlines: string of phone numbers
		private static function _checkHotlines($hotlines) {
			
			// Create an array that holds all the phone number with their corresponding organization
			$phoneNumbers = preg_split("/(\s*),(\s*)/", $hotlines);
			
			// Iterate through the phone numbers
			for ($i = 0; $i < count($phoneNumbers); $i++) { 

				// Get the phone number in the current iteration
				$num = substr($phoneNumbers[$i], strpos($phoneNumbers[$i], "=") + 1);
				
				// If the number given is invalid, return false
				if(self::_checkPhoneNumber($num) === false) { return false; }
			}

			return true;
		}
	}

	
	// $feedback = new Format();
	// echo $feedback->$organization_error;
	// echo Format::$organization_error;
	// echo "<br>";
	// print_r(Format::formatOrganizationContent("database/test.txt", "database/RECOVERY/test.txt"));
	// echo "<br>";
	// echo Format::$organization_error;
	// echo $feedback->$organization_error;
	// echo A::foo();
		
	// // The URL to get to database folder for Healthy Campus
	// $valid_path = "database/";

	// // Valid 'organization' columns
	// $valid_org_col = array("organization", "website", "category", "description", "users", "hoursOfOperation", "imageName", "phoneNumber", "email", "location", "geotag");

	// // Valid 'subcategory' columns
	// $valid_sub_col = array("category", "description", "hotlines");

	// // Valid category
	// $valid_cat = array("SEXUAL HEALTH", "MENTAL HEALTH", "PHYSICAL ACTIVITY", "ALCOHOL & OTHER DRUGS", "NUTRITION");

	// // Read the content of the text file found in the given path and do a rough format (does not check if the contents are valid)
	// function rawContent($pathName) {
	// 	// Get content (Remove the uunnecessary characters)
	// 	$string = substr(str_replace("\n", "", file_get_contents($pathName)), 7, -8);
	// 	// Split strings based on their organzation
	// 	$content = preg_split("/}(\s*),(\s*){/", $string);

	// 	$result = array();

	// 	// Iterate through the organizations		
	// 	for($i = 0; $i < count($content); $i++) {
	// 		$temparr = array();
			
	// 		// Split strings based on their organzation
	// 		$x = preg_split("/\"(\s*),(\s*)\"/", $content[$i]);

	// 		// Iterate through the organizations' contents (ie name, description, etc,.)
	// 		for($j = 0; $j < count($x); $j++) {
	// 			// Key
	// 			// Remove the first opening quotation mark that encloses the future KEY (if necessary)
	// 			$word = ($j == 0) ? preg_replace("/\"/", "", $x[$j], 1) : $x[$j];

	// 			// Find the closing quotation mark that encloses the future KEY
	// 			$length = strpos($word, "\"");

	// 			// Remove the unnecessary characters between the future KEY and save it
	// 			$key = preg_replace('/\s+/', '', substr($word, 0, $length));

	// 			// Value
	// 			// Remove the first half of the string (the KEY)
	// 			$word = substr($word, (strpos($word, ":") + 1));

	// 			// Remove the first opening quotation mark that encloses the future VALUE
	// 			$word = substr($word, (strpos($word, "\"") + 1));

	// 			// Remove the closing opening quotation mark that encloses the future VALUE (if necessary)
	// 			$value = ($j == (count($x) - 1)) ? strrev(substr(strrev($word), (strpos(strrev($word), "\"")) + 1)) : $word;

	// 			// Remove whitespace in the beginning and in the end of the string
	// 			$value = ltrim($value);
	// 			$value = rtrim($value);

	// 			// Add the key and value pair in the temporary array
	// 			$temparr[$key] = $value;

	// 		}
	// 		// Add all the organization's information in array
	// 		$result[$i] = $temparr;
			
	// 	}

	// 	return $result;
	// }

	// // Checks if the organization's name is all letters
	// function _checkOrganization($orgName) {
	// 	return ctype_alpha(str_replace(' ', '', $orgName));
	// }

	// // Checks if the URL follow the URL format
	// // NOTE: it does not check the whole web if it exists because it will considerably slowdown the website
	// function _checkWebsite($url) {
	// 	return filter_var($url, FILTER_VALIDATE_URL);
	// }

	// // Checks if the category listed is valid
	// function _checkCategory($selected, $categories) {
	// 	return in_array(strtoupper($selected), $categories);
	// }

	// // Checks if the users given is valid
	// function _checkUsers($user, $users) {
	// 	return in_array(strtoupper($user), $users);
	// }

	// // Checks if the format given for Hours of Operation is valid
	// function _checkHOF($HOF) {
	// 	$result = array("Bool" => true, "Error" => "n/a");

	// 	// Turn the HOF string to array
	// 	$time = (strpos($HOF, ",") == false) ? array($HOF) : preg_split("/,/", $HOF);

	// 	// List of valid days
	// 	$day = array("m", "tu", "w", "th", "f", "sa", "su");
	// 	for ($i = 0; $i < count($time); $i++) {
	// 		$isActive = false;
	// 		$skip = 0;

	// 		// Make sure to stop by '=' sign
	// 		$length = strlen(substr($time[$i], 0, strpos($time[$i], "="))) + 1;
	// 		for ($j = 0; $j < $length; $j++) {

	// 			// Ensure that it skips iteration on extra letters (if applicable) and the last index (because that's the '=' sign)
	// 			// Example: 'm' does not have extra letter but 'tu' has, so the iteration won't iterate on the extra letter which is 'u' in this case
	// 			if (((($isActive == true) && ($skip === $j))) || $j == ($length  - 1)) { continue; };
				
	// 			// If the current index is either a 't' or and 's' 
	// 			if ($time[$i][$j] == "t" || $time[$i][$j] == "s") {
					
	// 				// Ensures that it captures the whole 'day'
	// 				$selected =  $time[$i][$j] . $time[$i][$j + 1];

	// 				// Activate the skipping process mentioned above
	// 				$isActive = true;

	// 				// Mark the index that will be skipped
	// 				$skip = $j + 1;

	// 			} else {

	// 				// The current character
	// 				$selected =  $time[$i][$j];

	// 				// Deactivate the skipping process
	// 				$isActive = false;
	// 			}

	// 			// If any of the strings containts '=', skip it
	// 			if((strlen($selected) == 2 && $selected[1] == "=")) { continue; };

	// 			// Find the day (ie 'm', 'th', etc.,) and remove it from DAYS array
	// 			// This is to prevent repetition of days
	// 			if (($key = array_search($selected, $day)) !== false) {
	// 			    unset($day[$key]);
	// 			} else {
	// 				// Return FALSE if there's day repetition OR the day in invalid
	// 				$result["Bool"] = false;
	// 	 			$result["Error"] = "Invalid Format - hoursOfOperation: " . $HOF;
	// 	 			return $result;
	// 			}
	// 		}

	// 		// Gets the time range
	// 		$range = strtoupper(substr($time[$i], strpos($time[$i], "=") + 1));

	// 		// Returns FALSE if the value does not follow the correct time format
	// 		if (!($range == "allday") && !(preg_match("#([01]?[0-9]|2[0-3]):[0-5][0-9](am|pm)\-([01]?[0-9]|2[0-3]):[0-5][0-9](am|pm)$#", $range))) {
	// 			$result["Bool"] = false;
	//  			$result["Error"] = "Invalid Format - hoursOfOperation: " . $HOF;
	//  			return $result;
	// 	 	}

	// 	}
	// }

	// Checks if the number given is valid
	// function _checkPhoneNumber($number) {
	// 	return ctype_digit($number) && strlen($number) == 10;
	// }

	// echo _checkPhoneNumber();

	// // Checks if the email follows the email format
	// function _checkEmail($email) {
	// 	return filter_var($email, FILTER_VALIDATE_EMAIL);
	// }

	// // Checks if all the hotlines given are formatted correctly
	// function _checkHotlines($hotlines) {
	// 	$phoneNumbers = preg_split("/(\s*),(\s*)/", $hotlines);
		
	// 	for ($i = 0; $i < count($phoneNumbers); $i++) { 
	// 		$num = substr($phoneNumbers[$i], strpos($phoneNumbers[$i], "=") + 1);
	// 		if(_checkPhoneNumber($num) === false) { return false; }
	// 	}
	// 	return true;
	// }

	// // Return an array that lists which days are in the specific string
	// function _determineDays($string) {
	// 	$result = array();
	// 	$isActive = false;
	// 	$skip = 0;
	// 	for ($i = 0; $i < strlen($string); $i++) { 
	// 		// Ensure that it skips iteration on extra letters (if applicable)
	// 		// Example: 'm' does not have extra letter but 'tu' has, so the iteration won't iterate on the extra letter which is 'u' in this case
	// 		if ((($isActive == true) && ($skip === $i))) { continue; };

	// 		// If the current index is either a 't' or and 's' 
	// 		if($string[$i] == "t" || $string[$i] == "s") {

	// 			// Ensures that it captures the whole 'day'
	// 			$selected = $string[$i] . $string[$i + 1];
				
	// 			// Activate the skipping process mentioned above
	// 			$isActive = true;

	// 			// Mark the index that will be skipped				
	// 			$skip = $i + 1;

	// 		} else {

	// 			// The current character
	// 			$selected = $string[$i];

	// 			// Deactivate the skipping process
	// 			$isActive = false;
	// 		}

	// 		// Determine the day based on the abbreviation and add it to the RESULT
	// 		if ($selected == 'm') {
	// 			array_push($result, "Monday");
	// 		} elseif ($selected == 'tu') {
	// 			array_push($result, "Tuesday");
	// 		} elseif ($selected == 'w') {
	// 			array_push($result, "Wednesday");
	// 		} elseif ($selected == 'th') {
	// 			array_push($result, "Thursday");
	// 		} elseif ($selected == 'f') {
	// 			array_push($result, "Friday");
	// 		} elseif ($selected == 'sa') {
	// 			array_push($result, "Saturday");
	// 		} elseif ($selected == 'su') {
	// 			array_push($result, "Sunday");
	// 		}	

	// 	}

	// 	return $result;
	// }

	// // Format Description
	// function _formatDescription($description) {
	// 	return explode("|par|", $description);
	// }

	// // Format Hours of Operation
	// function _formatHOF($HOF) {
	// 	// Turn the HOF string to array
	// 	$time = (strpos($HOF, ",") == false) ? array($HOF) : preg_split("/,/", $HOF);

	// 	// Initalize the days
	// 	$hoursOfOperation = array("Monday" => "", "Tuesday" => "", "Wednesday" => "", "Thursday" => "", "Friday" => "", "Saturday" => "", "Sunday" => "");
	// 	for ($i = 0; $i < count($time); $i++) {
			
	// 		// Get the actualy days of the current string
	// 		$abbr = substr($time[$i], 0, strpos($time[$i], "="));
	// 		$days = _determineDays(strtoupper($abbr));

	// 		// Get the time range it specified for the current string
	// 		$range = strtoupper(substr($time[$i], strpos($time[$i], "=") + 1));

	// 		// Iterate through the days
	// 		for ($j = 0; $j < count($days); $j++) { 

	// 			// Populate the HOURSOFOPERATION
	// 			if ($range == "allday") {
	// 				$hoursOfOperation[$days[$j]] = "Open 24 hours";
	// 			} else {
	// 				$hoursOfOperation[$days[$j]] = $range;
	// 			}
	// 		}
	// 	}

	// 	// If any of the keys does not have value, put 'Closed' for their values
	// 	foreach ($hoursOfOperation as $key => $value) {
	// 		if ($value == "") {
	// 			$hoursOfOperation[$key] = "Closed";
	// 		}
	// 	}

	// 	return $hoursOfOperation;

	// }

	// // Format image path
	// function _formatImage($img) {
	// 	return "img/" . $img;
	// }

	// // Format phone number
	// function _formatPhoneNumber($num) {
	// 	$num = (strlen($num) > 10) ? substr($num, 2) : $num;
	// 	return "(" . substr($num, 0, 3) . ") " . substr($num, 3, 3) . " - " . substr($num, 6, 4);
	// }

	// // Format hotlines
	// function _formatHotlines($hotlines) {
	// 	$phoneNumbers = preg_split("/(\s*),(\s*)/", $hotlines);
	// 	$result = array();

	// 	for ($i = 0; $i < count($phoneNumbers); $i++) { 
	// 		$num = explode("=", $phoneNumbers[$i]);
	// 		$result[$num[0]] = _formatPhoneNumber($num[1]);
	// 	}

	// 	return $result;
	// }

	// // For each column given, check if they follow the format stated in README
	// function checkValidity($organization, $categories) {
	// 	$result = array("Bool" => true, "Error" => "n/a");
	// 	foreach ($organization as $key => $value) {
	// 	 	if($value == "") { continue; }
	// 	 	if($key == "organization") {
	// 	 		if(_checkOrganization($value) === 0) {
	// 	 			$result["Bool"] = false;
	// 	 			$result["Error"] = "Invalid Format - organization: " . $value;
	// 	 			return $result;
	// 	 		}
	// 	 	} elseif ($key == "website") {
	// 			if (_checkWebsite($value) === 0) {
	// 				$result["Bool"] = false;
	// 	 			$result["Error"] = "Invalid Format - website: " . $value;
	// 	 			return $result;
	// 			}
	// 	 	} elseif ($key == "category") {
	// 	 		if(_checkCategory($value, $categories) === 0) {
	// 	 			$result["Bool"] = false;
	// 	 			$result["Error"] = "Invalid Format - category: " . $value;
	// 	 			return $result;
	// 	 		}
	// 	 	} elseif ($key == "users") {
	// 	 		$valid_users = array("student", "faculty", "both");
	// 	 		if(_checkUsers($value, $valid_users) === 0) {
	// 	 			$result["Bool"] = false;
	// 	 			$result["Error"] = "Invalid Format - users: " . $value;
	// 	 			return $result;
	// 	 		}
	// 	 	} elseif ($key == "hoursOfOperation") {
	// 	 		_checkHOF($value);
	// 	 	} elseif ($key == "phoneNumber") {
	// 	 		$number = substr($value, 2, 10);
	// 	 		if(_checkPhoneNumber($number) === 0) {
	// 	 			$result["Bool"] = false;
	// 	 			$result["Error"] = "Invalid Format - phoneNumber: " . $number;
	// 	 			return $result;
	// 	 		}
	// 	 	} elseif ($key == "email)") {
	// 	 		if(_checkEmail($email) === 0) {
	// 	 		 	$result["Bool"] = false;
	// 	 			$result["Error"] = "Invalid Format - email: " . $value;
	// 	 			return $result;			
	// 	 		}
	// 	 	} elseif ($key == "hotlines") {
	// 	 		if(_checkHotlines($value) === 0)  {
	// 	 			$result["Bool"] = false;
	// 	 			$result["Error"] = "Invalid Format - hotlines: " . $value;
	// 	 			return $result;
	// 	 		}
	// 	 	}

	// 	}
	// 	return $result;
	// }

	// // Format the whole organization database
	// function formatOrganizationContent($original_path, $columns, $categories, $recovery_path) {
	// 	// Parsed JSON text file to be more readable
	// 	$org = rawContent($original_path);
	// 	$result = $org;

	// 	// Iterate through all the organization
	// 	for($i = 0; $i < count($org); $i++) {

	// 		// Iterate through an organization's information
	// 		for($j = 0; $j < count($columns); $j++){

	// 			// For the columns that are not filled out in the database, initialize each one to have and empty string
	// 			if(!(array_key_exists($columns[$j], $org[$i]))) {
	// 				$result[$i][$columns[$j]] = "";
	// 			}
	// 		}

	// 		// Check if content the value of the current organization is valid, if not return 'FAILED'
	// 		$check = checkValidity($result[$i], $categories);
	// 		if (!$check["Bool"]) {
				
	// 			// Update the 'corrupted' file with the recorvery file
	// 			copy($recovery_path, $original_path);

	// 			// Need to be able to run the recovery file

	// 			return "Error: Organization database failed to load! -- " . $check["Error"];
	// 		}
			
	// 		// Format Description
	// 		if ($result[$i]["description"] !== "") { $result[$i]["description"] = _formatDescription($result[$i]["description"]); };

	// 		// Format Hours of Operation
	// 		if ($result[$i]["hoursOfOperation"] !== "") { $result[$i]["hoursOfOperation"] = _formatHOF($result[$i]["hoursOfOperation"]); };

	// 		// Format Image Link
	// 		if ($result[$i]["imageName"] !== "") { $result[$i]["imageName"] = _formatImage($result[$i]["imageName"]); };

	// 		// Format Phone Number
	// 		if ($result[$i]["phoneNumber"] !== "") { $result[$i]["phoneNumber"] = _formatPhoneNumber($result[$i]["phoneNumber"]); };

	// 	}

	// 	// Update recovery files here
	// 	copy($original_path, $recovery_path);

	// 	return $result;
	// }

	// // Format the whole subcategory database
	// function formatSubcategoryContent($category, $columns, $categories) {
	// 	$result = $category;

	// 	// Iterate through all the category
	// 	for($i = 0; $i < count($category); $i++) {

	// 		// Iterate through an category's information
	// 		for($j = 0; $j < count($columns); $j++){

	// 			// For the columns that are not filled out in the database, initialize each one to have and empty string
	// 			if(!(array_key_exists($columns[$j], $category[$i]))) {
	// 				$result[$i][$columns[$j]] = "";
	// 			}
	// 		}
			
	// 		// Check if content the value of the current organization is valid, if not return 'FAILED'
	// 		if (!checkValidity($result[$i], $categories)["Bool"]) { return "Error: Subcategory database failed to load!"; };

	// 		// Format Description
	// 		if ($result[$i]["description"] !== "") { $result[$i]["description"] = _formatDescription($result[$i]["description"]); };

	// 		// Format Hotlines
	// 		if ($result[$i]["hotlines"] !== "") { $result[$i]["hotlines"] = _formatHotlines($result[$i]["hotlines"]); };
	// 	}


	// 	return $result;
	// }

	//Scans the database searching for resources by two categories -- the working group, and who can use the resources. May split into two functions soon, but this is good for now. 
	function get_user_resources($category, $user, $category_data){
		$results = array();

			foreach ($category_data as $value){
				if($value["category"] == $category && $value["users"] == $user){
					// echo "value organization: " . $value["organization"] . "--";
					// echo "value users: " . $value["users"] . "--";
					// echo "user: " . $user . "<br>";
					array_push($results, $value);
				}
			}
	return $results;
	}
  	
	// print_r(formatSubcategoryContent(rawContent("database/subcategory.txt"), $valid_sub_col, $valid_cat));
	// print_r(rawContent("database/subcategory.txt"));
	// print_r(formatOrganizationContent("database/test.txt", $valid_org_col, $valid_cat, "database/RECOVERY/test.txt"));
	// print_r(rawContent("database/organization.txt"));
	// print_r(formatOrganizationContent(rawContent("http://centaurus-8.ics.uci.edu:8926/hcampus/database/organization.txt"), $valid_org_col, $valid_cat));

?>