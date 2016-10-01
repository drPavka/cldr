#Task:

Write a command-line tool to parse CLDR [1] data and for a given language, print the languages of the world, ordered by number of speakers. For each language, trint the abbreviation of the language, the language name in English and the estimated number of speakers according to CLDR:
et Estonian 1234567
fi Finnish 7654321
Hint: you only need the supplementalData.xml file [2] for the calculations. You may use a library that wraps CLDR.

#Setup:
 + Install composer
 + Run  ``composer install --no-autoloader``
 + Run ``./cldr.php``
 
 #Comments:
 I'm using composer only for PHP version and php libraries validation. No need to use 3rd party console lib.  
 **get_languages_from**  not always determine languages' names correctly. As a variant  we can use DOM functions instead of SimpleXML and use DOMComment object to get language names from comments in file. 
  But IMHO it will look ugly.  