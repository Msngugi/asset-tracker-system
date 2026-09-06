
CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL,
  `app_name` varchar(100) DEFAULT 'AssetFlow',
  `app_email` varchar(100) DEFAULT NULL,
  `org_name` varchar(100) DEFAULT NULL,
  `org_address` varchar(255) DEFAULT NULL,
  `org_phone` varchar(20) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'PHP',
  `items_per_page` int(11) DEFAULT 20,
  `session_timeout` int(11) DEFAULT 30,
  `maintenance_mode` tinyint(4) DEFAULT 0,
  `require_strong_password` tinyint(4) DEFAULT 0,
  `password_expiry_days` int(11) DEFAULT 90,
  `max_login_attempts` int(11) DEFAULT 5,
  `lockout_duration` int(11) DEFAULT 30,
  `enable_2fa` tinyint(4) DEFAULT 0,
  `enable_logging` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `assets` (
  `asset_id` int(10) UNSIGNED NOT NULL,
  `asset_code` varchar(64) DEFAULT NULL,
  `asset_name` varchar(200) NOT NULL,
  `asset_model` varchar(255) NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `serial_number` varchar(150) DEFAULT NULL,
  `status` enum('available','in-use','under-maintenance','lost','damaged','retired') DEFAULT 'available',
  `asset_condition` enum('excellent','good','fair','poor','damaged') DEFAULT 'excellent',
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `notes` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `asset_categories` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `category_name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `asset_images` (
  `image_id` int(10) NOT NULL,
  `asset_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `image_filename` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `asset_logs` (
  `log_id` int(10) NOT NULL,
  `asset_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `log_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `borrow_asset` (
  `borrow_id` int(10) NOT NULL,
  `asset_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `borrow_date` date NOT NULL,
  `borrow_time` time NOT NULL,
  `expected_return_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `asset_condition_on_borrow` enum('excellent','good','fair','poor','damaged') NOT NULL DEFAULT 'excellent',
  `qr_code_scanned` varchar(255) NOT NULL,
  `status` enum('in-use','returned','overdue','lost','damaged') DEFAULT 'in-use',
  `reminder_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reminder_sent_date` datetime DEFAULT NULL,
  `overdue_notification_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




CREATE TABLE `departments` (
  `department_id` int(10) UNSIGNED NOT NULL,
  `department_code` varchar(50) NOT NULL,
  `department_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `email_logs` (
  `id` int(10) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `email_type` varchar(50) NOT NULL,
  `recipient_email` varchar(100) NOT NULL,
  `subject` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('success','failed') NOT NULL DEFAULT 'success',
  `error_message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `email_notifications` (
  `notification_id` int(10) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `borrow_id` int(10) NOT NULL,
  `notification_type` enum('reminder-1day','reminder-3days','reminder-5days','reminder-7days','reminder-14days','reminder-30days') DEFAULT 'reminder-1day',
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `scheduled_date` datetime NOT NULL,
  `is_sent` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `penalties` (
  `penalty_id` int(10) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `return_id` int(10) NOT NULL,
  `penalty_type` enum('overdue','damage','lost') NOT NULL,
  `penalty_amount` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','paid','waived') DEFAULT 'pending',
  `issued_date` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `qr_codes` (
  `qr_id` int(10) NOT NULL,
  `asset_id` int(10) UNSIGNED NOT NULL,
  `qr_code` varchar(255) NOT NULL,
  `image_filename` varchar(255) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `generated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_scanned` timestamp NOT NULL DEFAULT current_timestamp(),
  `scan_count` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `return_asset` (
  `return_id` int(10) NOT NULL,
  `borrow_id` int(10) DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `asset_id` int(10) UNSIGNED NOT NULL,
  `return_date` date NOT NULL,
  `return_time` time NOT NULL,
  `asset_condition_on_return` enum('excellent','good','fair','poor','damaged') DEFAULT 'excellent',
  `return_notes` text NOT NULL,
  `days_borrowed` int(10) NOT NULL,
  `is_overdue` tinyint(1) NOT NULL DEFAULT 0,
  `overdue_days` int(10) NOT NULL DEFAULT 0,
  `penalty_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `department_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `user_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Indexes for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`asset_id`),
  ADD UNIQUE KEY `asset_code` (`asset_code`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD UNIQUE KEY `asset_code_2` (`asset_code`),
  ADD KEY `idx_assets_category` (`category_id`),
  ADD KEY `idx_assets_assigned_to` (`assigned_to`);

--
-- Indexes for table `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `asset_images`
--
ALTER TABLE `asset_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `fk_asset_images_asset` (`asset_id`);

--
-- Indexes for table `asset_logs`
--
ALTER TABLE `asset_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_log_asset` (`asset_id`),
  ADD KEY `fk_log_user` (`user_id`);

--
-- Indexes for table `borrow_asset`
--
ALTER TABLE `borrow_asset`
  ADD PRIMARY KEY (`borrow_id`),
  ADD KEY `idx_borrow_status` (`status`),
  ADD KEY `idx_borrow_dates` (`borrow_date`,`expected_return_date`),
  ADD KEY `fk_borrow_asset_asset` (`asset_id`),
  ADD KEY `fk_borrow_asset_user` (`user_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `code` (`department_code`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD UNIQUE KEY `borrow_id` (`borrow_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `penalties`
--
ALTER TABLE `penalties`
  ADD PRIMARY KEY (`penalty_id`),
  ADD KEY `fk_penalty_user` (`user_id`),
  ADD KEY `fk_penalty_return` (`return_id`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`qr_id`),
  ADD KEY `fk_qr_asset` (`asset_id`);

--
-- Indexes for table `return_asset`
--
ALTER TABLE `return_asset`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `fk_asset_return` (`asset_id`),
  ADD KEY `idx_return_date` (`return_date`),
  ADD KEY `fk_return_borrow` (`borrow_id`),
  ADD KEY `fk_return_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_logs_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `asset_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asset_categories`
--
ALTER TABLE `asset_categories`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asset_images`
--
ALTER TABLE `asset_images`
  MODIFY `image_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asset_logs`
--
ALTER TABLE `asset_logs`
  MODIFY `log_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrow_asset`
--
ALTER TABLE `borrow_asset`
  MODIFY `borrow_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_notifications`
--
ALTER TABLE `email_notifications`
  MODIFY `notification_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penalties`
--
ALTER TABLE `penalties`
  MODIFY `penalty_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `qr_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_asset`
--
ALTER TABLE `return_asset`
  MODIFY `return_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `fk_asset_assigned_user` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_assets_category` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `asset_images`
--
ALTER TABLE `asset_images`
  ADD CONSTRAINT `fk_asset_images_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `asset_logs`
--
ALTER TABLE `asset_logs`
  ADD CONSTRAINT `fk_asset_logs_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_asset_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`),
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `borrow_asset`
--
ALTER TABLE `borrow_asset`
  ADD CONSTRAINT `fk_borrow_asset_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_borrow_asset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_borrow_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `fk_email_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `penalties`
--
ALTER TABLE `penalties`
  ADD CONSTRAINT `fk_penalty_return` FOREIGN KEY (`return_id`) REFERENCES `return_asset` (`return_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_penalty_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `fk_qr_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `return_asset`
--
ALTER TABLE `return_asset`
  ADD CONSTRAINT `fk_return_borrow` FOREIGN KEY (`borrow_id`) REFERENCES `borrow_asset` (`borrow_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_return_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `fk_user_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;


