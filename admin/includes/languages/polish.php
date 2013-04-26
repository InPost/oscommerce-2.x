<?php
/*
  $Id: english.php,v 1.106 2003/08/18 12:18:31 ramroz Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Publikowane na zasadach licencji GNU General Public License

  T³umaczenie: Rafa³ Mróz ramroz@optimus.pl
  http://www.portalik.com

*/


// zobacz w katalogu $PATH_LOCALE/locale dostêpne lokalizacje..
// w RedHacie powinno byæ 'pl_PL'
// we FreeBSD sprawd· 'pl_PL.ISO_8859-2'
// w Windows spróbuj 'pl', lub 'Polski'



setlocale(LC_TIME, 'pl_PL.ISO_8859-2');
define('DATE_FORMAT_SHORT', '%d %m %Y');  // u¿ywane przy strftime()
define('DATE_FORMAT_LONG', '%A, %d %B %Y'); // u¿ywane przy strftime()
define('DATE_FORMAT', 'd/m/Y'); // u¿ywane przy date()
define('PHP_DATE_TIME_FORMAT', 'd/m/Y H:i:s'); // u¿ywane przy date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');


////
// Zwraca sformatowan± datê jako raw format
// $date powinna mieæ format dd/mm/yyyy
// format raw date ma postaæ YYYYMMDD, lub DDMMYYYY
function tep_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 0, 2) . substr($date, 3, 2) . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . substr($date, 3, 2) . substr($date, 0, 2);
  }
}


// Global entries for the <html> tag
define('HTML_PARAMS','dir="ltr" lang="pl"');

// charset for web pages and emails
define('CHARSET', 'iso-8859-2');

// page title
define('TITLE', STORE_NAME);

// header text in includes/header.php
define('HEADER_TITLE_TOP', 'Administracja');
define('HEADER_TITLE_SUPPORT_SITE', 'Strona Wsparcia');
define('HEADER_TITLE_ONLINE_CATALOG', 'Sklep');
define('HEADER_TITLE_ADMINISTRATION', 'Administracja');

// text for gender
define('MALE', 'Mê¿czyzna');
define('FEMALE', 'Kobieta');

// text for date of birth example
define('DOB_FORMAT_STRING', 'dd/mm/rrrr');

// configuration box text in includes/boxes/configuration.php
define('BOX_CONFIGURATION_ADMINISTRATORS', 'Administratorzy');
define('BOX_HEADING_CONFIGURATION', 'Konfiguracja');
define('BOX_CONFIGURATION_MYSTORE', 'Mój Sklep');
define('BOX_CONFIGURATION_LOGGING', 'Logowanie');
define('BOX_CONFIGURATION_CACHE', 'Cache');

// modules box text in includes/boxes/modules.php
define('BOX_HEADING_MODULES', 'Modu³y');
define('BOX_MODULES_PAYMENT', 'P³atno¶æ');
define('BOX_MODULES_SHIPPING', 'Wysy³ka');
define('BOX_MODULES_ORDER_TOTAL', 'Suma Zamówienia');

// categories box text in includes/boxes/catalog.php
define('BOX_HEADING_CATALOG', 'Sklep');
define('BOX_CATALOG_CATEGORIES_PRODUCTS', 'Kategorie/Produkty');
define('BOX_CATALOG_CATEGORIES_PRODUCTS_ATTRIBUTES', 'Cechy Produktów');
define('BOX_CATALOG_MANUFACTURERS', 'Producenci');
define('BOX_CATALOG_REVIEWS', 'Recenzje');
define('BOX_CATALOG_SPECIALS', 'Promocje');
define('BOX_CATALOG_PRODUCTS_EXPECTED', 'Produkty Oczekiwane');

// customers box text in includes/boxes/customers.php
define('BOX_HEADING_CUSTOMERS', 'Klienci');
define('BOX_CUSTOMERS_CUSTOMERS', 'Klienci');
define('BOX_CUSTOMERS_ORDERS', 'Zamówienia');
// start inpostparcels
define('BOX_CUSTOMERS_INPOSTPARCELS', 'Paczkomaty InPost');
// end inpostparcels


// taxes box text in includes/boxes/taxes.php
define('BOX_HEADING_LOCATION_AND_TAXES', 'Miejscowo¶ci / Podatki');
define('BOX_TAXES_COUNTRIES', 'Kraje');
define('BOX_TAXES_ZONES', 'Strefy');
define('BOX_TAXES_GEO_ZONES', 'Strefy Podatkowe');
define('BOX_TAXES_TAX_CLASSES', 'Klasy Podatków');
define('BOX_TAXES_TAX_RATES', 'Stawki Podatków');

// reports box text in includes/boxes/reports.php
define('BOX_HEADING_REPORTS', 'Raporty');
define('BOX_REPORTS_PRODUCTS_VIEWED', 'Najczê¶ciej Wy¶wietlane');
define('BOX_REPORTS_PRODUCTS_PURCHASED', 'Najczê¶ciej Kupowane');
define('BOX_REPORTS_ORDERS_TOTAL', 'Najlepsi Klienci');

// tools text in includes/boxes/tools.php
define('BOX_HEADING_TOOLS', 'Narzêdzia');
define('BOX_TOOLS_BACKUP', 'Archiwizacja Bazy Danych');
define('BOX_TOOLS_BANNER_MANAGER', 'Zadz±dzanie Banerami');
define('BOX_TOOLS_CACHE', 'Kontrola Cache');
define('BOX_TOOLS_DEFINE_LANGUAGE', 'Definiowanie Jêzyków');
define('BOX_TOOLS_FILE_MANAGER', 'Eksplorator Plików');
define('BOX_TOOLS_MAIL', 'Mailing');
define('BOX_TOOLS_NEWSLETTER_MANAGER', 'Zadz±dzanie Newsletterem');
define('BOX_TOOLS_SERVER_INFO', 'Informacje o Serwerze');
define('BOX_TOOLS_WHOS_ONLINE', 'Kto Jest w Sklepie');

// localizaion box text in includes/boxes/localization.php
define('BOX_HEADING_LOCALIZATION', 'Lokalizacja');
define('BOX_LOCALIZATION_CURRENCIES', 'Waluty');
define('BOX_LOCALIZATION_LANGUAGES', 'Jêzyki');
define('BOX_LOCALIZATION_ORDERS_STATUS', 'Statusy Zamówienia');

// javascript messages
define('JS_ERROR', 'Podczas przetwarzania formularza wyst±pi³y b³êdy!\nProszê poprawiæ nastêpuj±ce dane:\n\n');

define('JS_OPTIONS_VALUE_PRICE', '* Nowa cecha produktu musi mieæ warto¶æ\n');
define('JS_OPTIONS_VALUE_PRICE_PREFIX', '* Cena dla nowej cechy produktu musi mieæ prefiks\n');

define('JS_PRODUCTS_NAME', '* Nowy produkt musi mieæ podan± nazwê\n');
define('JS_PRODUCTS_DESCRIPTION', '* Nowy produkt musi miec podany opis\n');
define('JS_PRODUCTS_PRICE', '* Nowy produkt musi mieæ podan± cenê\n');
define('JS_PRODUCTS_WEIGHT', '* Nowy produkt musi mieæ podan± wagê\n');
define('JS_PRODUCTS_QUANTITY', '* Nowy produkt musi mieæ podan± ilo¶æ sztuk\n');
define('JS_PRODUCTS_MODEL', '* Nowy produkt musi mieæ podany model\n');
define('JS_PRODUCTS_IMAGE', '* Nowy produkt musi mieæ obrazek\n');

define('JS_SPECIALS_PRODUCTS_PRICE', '* Dla tego produktu musi byæ ustalona nowa cena\n');

define('JS_GENDER', '* Musisz wybraæ p³eæ.\n');
define('JS_FIRST_NAME', '* Imiê musi mieæ przynajmniej ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' znaków.\n');
define('JS_LAST_NAME', '* Nazwisko musi mieæ przynajmniej ' . ENTRY_LAST_NAME_MIN_LENGTH . ' znaków.\n');
define('JS_DOB', '* Data urodzenia  musi mieæ format: xx/xx/xxxx (dzieñ/miesi±c/rok).\n');
define('JS_EMAIL_ADDRESS', '* Adres email  musi mieæ przynajmniej ' . ENTRY_EMAIL_ADDRESS_MIN_LENGTH . ' znaków.\n');
define('JS_ADDRESS', '* Ulica  musi mieæ przynajmniej ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' znaków.\n');
define('JS_POST_CODE', '* Kod pocztowy  musi mieæ ' . ENTRY_POSTCODE_MIN_LENGTH . ' znaków.\n');
define('JS_CITY', '* Miasto  musi mieæ przynajmniej ' . ENTRY_CITY_MIN_LENGTH . ' znaków.\n');
define('JS_STATE', '* Musisz wybraæ województwo.\n');
define('JS_STATE_SELECT', '-- Wybierz --');
define('JS_ZONE', '* Dla tego kraju musisz wybraæ \'Województwo\' z rozwijanej listy.');
define('JS_COUNTRY', '* Musisz wybraæ \'Kraj\'.\n');
define('JS_TELEPHONE', '* Nr telefonu  musi mieæ przynajmniej ' . ENTRY_TELEPHONE_MIN_LENGTH . ' znaków.\n');
define('JS_PASSWORD', '* Has³o i Potwierdzenie Has³a musi byæ identyczne i musi mieæ przynajmniej ' . ENTRY_PASSWORD_MIN_LENGTH . ' znaków.\n');

define('JS_ORDER_DOES_NOT_EXIST', 'Zamówienie nr %s nie istnieje!');

define('CATEGORY_COMPANY', 'Dane Firmy');
define('CATEGORY_PERSONAL', 'Dane Osobowe');
define('CATEGORY_ADDRESS', 'Dane Teleadresowe');
define('CATEGORY_CONTACT', 'Dane Kontaktowe');
define('CATEGORY_OPTIONS', 'Opcje');

define('ENTRY_GENDER', 'P³eæ:');
define('ENTRY_GENDER_ERROR', '&nbsp;<span class="errorText">wymagane</span>');
define('ENTRY_FIRST_NAME', 'Imiê:');
define('ENTRY_FIRST_NAME_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' zn.</span>');
define('ENTRY_LAST_NAME', 'Nazwisko:');
define('ENTRY_LAST_NAME_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_LAST_NAME_MIN_LENGTH . ' zn.</span>');
define('ENTRY_DATE_OF_BIRTH', 'Data Urodzenia:');
define('ENTRY_DATE_OF_BIRTH_ERROR', '&nbsp;<span class="errorText">(np. 21/05/1970)</span>');
define('ENTRY_EMAIL_ADDRESS', 'Adres E-Mail:');
define('ENTRY_EMAIL_ADDRESS_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_EMAIL_ADDRESS_MIN_LENGTH . ' zn.</span>');
define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', '&nbsp;<span class="errorText">Ten adres email nie jest poprawny!</span>');
define('ENTRY_EMAIL_ADDRESS_ERROR_EXISTS', '&nbsp;<span class="errorText">Taki adres email ju¿ istnieje w naszej bazie!</span>');
define('ENTRY_COMPANY', 'Nazwa Firmy:');
define('ENTRY_COMPANY_ERROR', '');
define('ENTRY_NIP', 'Numer NIP:');
define('ENTRY_NIP_ERROR', '');
define('ENTRY_STREET_ADDRESS', 'Ulica:');
define('ENTRY_STREET_ADDRESS_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' zn.</span>');
define('ENTRY_SUBURB', 'Dzielnica:');
define('ENTRY_SUBURB_ERROR', '');
define('ENTRY_POST_CODE', 'Kod Pocztowy:');
define('ENTRY_POST_CODE_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_POSTCODE_MIN_LENGTH . ' zn.</span>');
define('ENTRY_CITY', 'Miasto:');
define('ENTRY_CITY_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_CITY_MIN_LENGTH . ' zn.</span>');
define('ENTRY_STATE', 'Województwo:');
define('ENTRY_STATE_ERROR', '&nbsp;<span class="errorText">wymagane</span>');
define('ENTRY_COUNTRY', 'Kraj:');
define('ENTRY_COUNTRY_ERROR', '');
define('ENTRY_TELEPHONE_NUMBER', 'Nr Telefonu:');
define('ENTRY_TELEPHONE_NUMBER_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_TELEPHONE_MIN_LENGTH . ' zn.</span>');
define('ENTRY_FAX_NUMBER', 'Nr Faksu:');
define('ENTRY_FAX_NUMBER_ERROR', '');
define('ENTRY_NEWSLETTER', 'Newsletter:');
define('ENTRY_NEWSLETTER_YES', 'Zapisany');
define('ENTRY_NEWSLETTER_NO', 'Wypisany');
define('ENTRY_NEWSLETTER_ERROR', '');

// images
define('IMAGE_ANI_SEND_EMAIL', 'Wys³anie Emaila');
define('IMAGE_BACK', 'Wróæ');
define('IMAGE_BACKUP', 'Archiwizacja');
define('IMAGE_CANCEL', 'Anuluj');
define('IMAGE_CONFIRM', 'Zatwierd·');
define('IMAGE_COPY', 'Kopiuj');
define('IMAGE_COPY_TO', 'Kopiuj Do');
define('IMAGE_DEFINE', 'Definiuj');
define('IMAGE_DELETE', 'Usuñ');
define('IMAGE_EDIT', 'Edytuj');
define('IMAGE_EMAIL', 'Email');
define('IMAGE_FILE_MANAGER', 'Eksplorator Plików');
define('IMAGE_ICON_STATUS_GREEN', 'W³±czone');
define('IMAGE_ICON_STATUS_GREEN_LIGHT', 'W³±cz');
define('IMAGE_ICON_STATUS_RED', 'Wy³±czone');
define('IMAGE_ICON_STATUS_RED_LIGHT', 'Wy³±cz');
define('IMAGE_ICON_INFO', 'Informacje');
define('IMAGE_INSERT', 'Wstaw');
define('IMAGE_LOCK', 'Zablokuj');
define('IMAGE_MODULE_INSTALL', 'Zainstaluj Modu³');
define('IMAGE_MODULE_REMOVE', 'Odinstaluj Modu³');
define('IMAGE_MOVE', 'Przenie¶');
define('IMAGE_NEW_BANNER', 'Nowy Baner');
define('IMAGE_NEW_CATEGORY', 'Nowa Kategoria');
define('IMAGE_NEW_COUNTRY', 'Nowy Kraj');
define('IMAGE_NEW_CURRENCY', 'Nowa Waluta');
define('IMAGE_NEW_FILE', 'Nowy Plik');
define('IMAGE_NEW_FOLDER', 'Nowy Katalog');
define('IMAGE_NEW_LANGUAGE', 'Nowy Jêzyk');
define('IMAGE_NEW_NEWSLETTER', 'Nowy Newsletter');
define('IMAGE_NEW_PRODUCT', 'Nowy Produkt');
define('IMAGE_NEW_TAX_CLASS', 'Nowa Klasa Podatku');
define('IMAGE_NEW_TAX_RATE', 'Nowa Stawka Podatku');
define('IMAGE_NEW_TAX_ZONE', 'Nowa Strefa Podatkowa');
define('IMAGE_NEW_ZONE', 'Nowa Strefa');
define('IMAGE_ORDERS', 'Zamówienia');
define('IMAGE_ORDERS_INVOICE', 'Faktura');
define('IMAGE_ORDERS_PACKINGSLIP', 'List Przewozowy');
define('IMAGE_PREVIEW', 'Podgl±d');
define('IMAGE_RESTORE', 'Przywróæ');
define('IMAGE_RESET', 'Resetuj');
define('IMAGE_SAVE', 'Zapisz');
define('IMAGE_SEARCH', 'Szukaj');
define('IMAGE_SELECT', 'Wybierz');
define('IMAGE_SEND', 'Wy¶lij');
define('IMAGE_SEND_EMAIL', 'Wy¶lij Email');
define('IMAGE_UNLOCK', 'Odblokuj');
define('IMAGE_UPDATE', 'Aktualizuj');
define('IMAGE_UPDATE_CURRENCIES', 'Aktualizuj Kurs Wymiany');
define('IMAGE_UPLOAD', 'Upload');

define('ICON_CROSS', 'Fa³sz');
define('ICON_CURRENT_FOLDER', 'Bie¿±cy Katalog');
define('ICON_DELETE', 'Usuñ');
define('ICON_ERROR', 'B³±d');
define('ICON_FILE', 'Plik');
define('ICON_FILE_DOWNLOAD', 'Download');
define('ICON_FOLDER', 'Katalog');
define('ICON_LOCKED', 'Zablokowany');
define('ICON_PREVIOUS_LEVEL', 'Poprzedni Poziom');
define('ICON_PREVIEW', 'Podgl±d');
define('ICON_STATISTICS', 'Statystyki');
define('ICON_SUCCESS', 'Powiod³o Siê');
define('ICON_TICK', 'Prawda');
define('ICON_UNLOCKED', 'Odblokowany');
define('ICON_WARNING', 'Ostrze¿enie');

// constants for use in tep_prev_next_display function
define('TEXT_RESULT_PAGE', 'Strona %s z %d');
define('TEXT_DISPLAY_NUMBER_OF_BANNERS', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> banerów)');
define('TEXT_DISPLAY_NUMBER_OF_COUNTRIES', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> krajów)');
define('TEXT_DISPLAY_NUMBER_OF_CUSTOMERS', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> klientów)');
define('TEXT_DISPLAY_NUMBER_OF_CURRENCIES', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> walut)');
define('TEXT_DISPLAY_NUMBER_OF_LANGUAGES', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> jêzyków)');
define('TEXT_DISPLAY_NUMBER_OF_MANUFACTURERS', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> producentów)');
define('TEXT_DISPLAY_NUMBER_OF_NEWSLETTERS', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> newsletterów)');
define('TEXT_DISPLAY_NUMBER_OF_ORDERS', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> zamówieñ)');
define('TEXT_DISPLAY_NUMBER_OF_ORDERS_STATUS', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> statusów zamówienia)');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> produktów)');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS_EXPECTED', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> produktów oczekiwanych)');
define('TEXT_DISPLAY_NUMBER_OF_REVIEWS', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> recenzji produktów)');
define('TEXT_DISPLAY_NUMBER_OF_SPECIALS', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> produktów w promocji)');
define('TEXT_DISPLAY_NUMBER_OF_TAX_CLASSES', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> klas podatku)');
define('TEXT_DISPLAY_NUMBER_OF_TAX_ZONES', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> stref podatkowych)');
define('TEXT_DISPLAY_NUMBER_OF_TAX_RATES', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> stawek podatku)');
define('TEXT_DISPLAY_NUMBER_OF_ZONES', 'Wy¶wietlono od <b>%d</b> do <b>%d</b> (z <b>%d</b> stref)');

define('PREVNEXT_BUTTON_PREV', '&lt;&lt;');
define('PREVNEXT_BUTTON_NEXT', '&gt;&gt;');

define('TEXT_DEFAULT', 'domy¶lny');
define('TEXT_SET_DEFAULT', 'Ustaw jako domy¶lne');
define('TEXT_FIELD_REQUIRED', '&nbsp;<span class="fieldRequired">* Wymagane</span>');

define('ERROR_NO_DEFAULT_CURRENCY_DEFINED', 'B³±d: Obecnie nie ma ustawionej domy¶lnej waluty. Proszê jak±¶ wybraæ: Panel Administracyjny->Lokalizacja->Waluty');

define('TEXT_CACHE_CATEGORIES', 'Kategorie');
define('TEXT_CACHE_MANUFACTURERS', 'Producenci');
define('TEXT_CACHE_ALSO_PURCHASED', 'Modu³ Zakupiono Równie¿');

define('TEXT_NONE', '--brak--');
define('TEXT_TOP', 'Top');

define('ERROR_DESTINATION_DOES_NOT_EXIST', 'B³±d: Obiekt docelowy  nie istnieje.');
define('ERROR_DESTINATION_NOT_WRITEABLE', 'B³±d: Od obiektu docelowego nie mo¿na zapisywaæ.');
define('ERROR_FILE_NOT_SAVED', 'B³±d: Wgrywany plik nie zosta³ zapisany.');
define('ERROR_FILETYPE_NOT_ALLOWED', 'B³±d: Wgrywanie tego rodzaju plików jest zabronione.');
define('SUCCESS_FILE_SAVED_SUCCESSFULLY', 'Powiod³o siê: Wgrywany plik zosta³ zapisany.');
define('WARNING_NO_FILE_UPLOADED', 'Uwaga: Nie wgrano pliku.');
define('WARNING_FILE_UPLOADS_DISABLED', 'Uwaga: Wgrywanie plików na serwer jest zablokowane w pliku konfiguracyjnym PHP php.ini.');

define('TEXT_DISPLAY_NUMBER_OF_ARTICLES', 'Wyswietlanie <b>%d</b> to <b>%d</b> (z <b>%d</b> artyku³ów)');
define('BOX_CATALOG_DEFINE_MAINPAGE', 'Edycja strony g³ównej');


// header text in includes/header.php
define('HEADER_TITLE_ACCOUNT', 'Moje konto');
define('HEADER_TITLE_LOGOFF', 'Wyloguj');

// Admin Account
define('BOX_HEADING_MY_ACCOUNT', 'Moje konto');

// configuration box text in includes/boxes/administrator.php
define('BOX_HEADING_ADMINISTRATOR', 'Administracja');
define('BOX_ADMINISTRATOR_MEMBERS', 'Grupy Cz³onkowskie');
define('BOX_ADMINISTRATOR_MEMBER', 'Osoby uprawnione');
define('BOX_ADMINISTRATOR_BOXES', 'Dostêp do plików');

// images
define('IMAGE_FILE_PERMISSION', 'Uprawnienia do plików');
define('IMAGE_GROUPS', 'Lista Grup');
define('IMAGE_INSERT_FILE', 'Dodaj plik');
define('IMAGE_MEMBERS', 'Lista Cz³onków');
define('IMAGE_NEW_GROUP', 'Nowa Grupa');
define('IMAGE_NEW_MEMBER', 'Nowy Cz³onek');
define('IMAGE_NEXT', 'Dalej');

// constants for use in tep_prev_next_display function
define('TEXT_DISPLAY_NUMBER_OF_FILENAMES', 'Wy¶wietlanie <b>%d</b> do <b>%d</b> (z <b>%d</b> plików)');
define('TEXT_DISPLAY_NUMBER_OF_MEMBERS', 'Wy¶wietlanie <b>%d</b> do <b>%d</b> (z <b>%d</b> Cz³onków)');

define('ERROR_FILE_NOT_WRITEABLE', 'Nie mo¿na zapisaæ zmian w pliku');
  define('BOX_HEADING_INFORMATION', 'Informacje');
  define('BOX_INFORMATION_MANAGER', 'Zarz±dzanie');
  
define('ENTRY_STREET_ADDRESS_DOM_MIESZKANIE', 'Numer Mieszkania');
define('ENTRY_STREET_ADDRESS_DOM_MIESZKANIE_TEXT', '');
define('ENTRY_STREET_ADDRESS_DOM', 'Numer Domu');
define('ENTRY_STREET_ADDRESS_DOM_TEXT', '*');

define('ENTRY_GMINA', 'Gmina');
define('ENTRY_GMINA_TEXT', '');
define('ENTRY_POWIAT', 'Powiat');
define('ENTRY_POWIAT_TEXT', '*');
define('BOX_CATALOG_EASYPOPULATE', 'Import/export CSV');
define('BOX_CATALOG_FEATURED', 'Produkty Polecane');
define('BOX_CATALOG_PRODUCTS_UPDATE', 'Ceny/wagi/magazyn');
define('BOX_REPORTS_STOCK_LEVEL', 'Braki Magazynowe');
define('BOX_REPORTS_CUSTOMER_STATS', 'Nowi Klienci');
define('BOX_TOOLS_BATCH_CENTER', 'Centrum Druku');
define('BOX_REPORTS_ORDERS_TRACKING', '¦ledzenie Zamówieñ');
define('BOX_REPORTS_ORDERS_TRACKING_ZONES', 'Zamówienia wg Strefy');

?>