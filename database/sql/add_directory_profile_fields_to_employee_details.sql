ALTER TABLE `employee_details`
  ADD COLUMN `directory_about` text NULL AFTER `about`,
  ADD COLUMN `linkedin_url` varchar(255) NULL AFTER `skills`,
  ADD COLUMN `portfolio_url` varchar(255) NULL AFTER `linkedin_url`,
  ADD COLUMN `facebook_url` varchar(255) NULL AFTER `portfolio_url`,
  ADD COLUMN `instagram_url` varchar(255) NULL AFTER `facebook_url`,
  ADD COLUMN `x_url` varchar(255) NULL AFTER `instagram_url`,
  ADD COLUMN `cv_path` varchar(255) NULL AFTER `x_url`;
