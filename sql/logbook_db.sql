/* 

 Source Server         : xampp
 Source Server Type    : MySQL
 Source Server Version : 100427
 Source Host           : localhost:3306
 Source Schema         : logbook_db

 Target Server Type    : MySQL
 Target Server Version : 100427
 File Encoding         : 65001

 Date: 23/04/2023 19:52:42
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for logs_tbl
-- ----------------------------
DROP TABLE IF EXISTS `logs_tbl`;
CREATE TABLE `logs_tbl`  (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `account_no` int NOT NULL,
  `date_entry` date NOT NULL DEFAULT curdate,
  `time_in` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `time_out` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('ongoing','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ongoing',
  PRIMARY KEY (`log_id`) USING BTREE,
  INDEX `account_no`(`account_no` ASC) USING BTREE,
  CONSTRAINT `logs_tbl_ibfk_1` FOREIGN KEY (`account_no`) REFERENCES `personnel_tbl` (`account_no`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for personnel_tbl
-- ----------------------------
DROP TABLE IF EXISTS `personnel_tbl`;
CREATE TABLE `personnel_tbl`  (
  `account_no` int NOT NULL AUTO_INCREMENT,
  `personnel_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `account_status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`account_no`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- View structure for entry_logs
-- ----------------------------
DROP VIEW IF EXISTS `entry_logs`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `entry_logs` AS SELECT 
	logs_tbl.account_no,
	logs_tbl.log_id,
	personnel_tbl.personnel_name,
	personnel_tbl.department,
	personnel_tbl.position,
	logs_tbl.date_entry,
	logs_tbl.time_in,
	logs_tbl.time_out
FROM personnel_tbl
JOIN logs_tbl on logs_tbl.account_no = personnel_tbl.account_no ;

SET FOREIGN_KEY_CHECKS = 1;
