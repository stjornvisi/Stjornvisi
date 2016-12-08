# 0007.sql
# Adds deleted column to User table for safe deletes
# Alters view for GroupUser. Adds deleted field from User table

ALTER TABLE `User` ADD deleted TINYINT UNSIGNED NOT NULL DEFAULT 0;

CREATE OR REPLACE VIEW `GroupUser` AS select `U`.`id` AS `id`,`U`.`name` AS `name`,`U`.`passwd` AS `passwd`,`U`.`email` AS `email`,`U`.`title` AS `title`,`U`.`created_date` AS `created_date`,`U`.`modified_date` AS `modified_date`,`U`.`frequency` AS `frequency`,`U`.`is_admin` AS `is_admin`,`U`.`deleted` as `deleted`,`CU`.`company_id` AS `company_id`,`C`.`name` AS `company_name`,`G`.`group_id` AS `group_id`,`G`.`type` AS `type` from (((`User` `U` join `Group_has_User` `G` on((`U`.`id` = `G`.`user_id`))) join `CompanyUser` `CU` on((`U`.`id` = `CU`.`user_id`))) join `Company` `C` on((`CU`.`company_id` = `C`.`id`))) order by `G`.`type` desc,`U`.`name`