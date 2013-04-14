/* Set passwords to md5 format
 * You must insert this one time, and one time only
 * Make sure that you backup the database
 */
UPDATE `playsms_tblUser` SET `password`=md5(`password`) ;
