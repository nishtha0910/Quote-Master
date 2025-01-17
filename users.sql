--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

-- --------------------------------------------------------------------------------------------------------
-- This is a test user account that you can use.  
-- The password has the value of: weak_password
-- you can use the password_hash() function from within phpMyAdmin
-- --------------------------------------------------------------------------------------------------------

INSERT INTO `users` (`user_id`, `username`, `password`) VALUES
(1, 'mohawk_user', '$2y$10$lJZhuFKGlAGIFoOXMS34D.suUB40iZ1UoIhPWfv0jBkDsQwO.NYzy');

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;



CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `quote_id` int(11) NOT NULL,
  PRIMARY KEY (`favorite_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`quote_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD UNIQUE KEY `IX_COMP` (`user_id`,`quote_id`);

