# Insert examples for tables


#### Jobs table
```sql
INSERT INTO Jobs (name, createdBy, modifiedBy)
VALUES ("Informatik", 1, 1);
```

#### Jobs_users tabel
```sql 
INSERT INTO users_jobs (userId, jobId, createdBy, modifiedBy) VALUES (1, 1, 1, 1);
```