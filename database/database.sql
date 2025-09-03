CREATE TABLE users (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','member','viewer') DEFAULT 'member',
  avatar VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE projects (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  owner_id BIGINT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE labels (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT, -- nullable if label global
  name VARCHAR(100) NOT NULL,
  color VARCHAR(7),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tasks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  creator_id BIGINT NOT NULL,
  assignee_id BIGINT,
  project_id BIGINT,
  parent_task_id BIGINT,
  status ENUM('todo','in_progress','done','archived') DEFAULT 'todo',
  priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
  start_date DATETIME,
  due_date DATETIME,
  recurrence_rule VARCHAR(255), -- e.g. rrule string or simple enum
  position INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (creator_id) REFERENCES users(id),
  FOREIGN KEY (assignee_id) REFERENCES users(id),
  FOREIGN KEY (project_id) REFERENCES projects(id),
  FOREIGN KEY (parent_task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

CREATE TABLE task_labels (
  task_id BIGINT,
  label_id BIGINT,
  PRIMARY KEY (task_id,label_id),
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
  FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
);

CREATE TABLE comments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  task_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE attachments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  task_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  filename VARCHAR(255) NOT NULL,
  path VARCHAR(512) NOT NULL,
  size INT,
  mime VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE audit_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  entity_type VARCHAR(50),
  entity_id BIGINT,
  user_id BIGINT,
  action VARCHAR(50),
  changes JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);