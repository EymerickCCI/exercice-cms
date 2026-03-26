1. Creation dépôt GitHub
2. Installation symfony
3. Creation BDD

4. Ajouts user
5. Ajouts des entités : 

User :
-> id INT NOT NULL
-> email STRING(255) NOT NULL
-> roles JSON NOT NULL
-> password STRING(255) NOT NULL
-> createdAt DATETIME_IMMUTABLE NOT NULL
-> updatedAt DATETIME_IMMUTABLE NOT NULL
-> articles ONE_TO_MANY → Article
-> comments ONE_TO_MANY → Commentaire

Category :

-> id INT NOT NULL
-> name STRING(255) NOT NULL
-> slug STRING(255) NOT NULL
-> articles ONE_TO_MANY → Article

Tag :
-> id INT NOT NULL
-> name STRING(255) NOT NULL
-> articles MANY_TO_MANY → Article

Page :
-> id INT NOT NULL
-> title STRING(255) NOT NULL
-> slug STRING(255) NOT NULL UNIQUE
-> content TEXT NOT NULL
-> metaDescription STRING(255) NULL
-> parent MANY_TO_ONE → Page NULLABLE (self-relation)
-> createdAt DATETIME_IMMUTABLE NOT NULL
-> updatedAt DATETIME_IMMUTABLE NOT NULL
-> isPublished BOOLEAN NOT NULL

Article :
-> id INT NOT NULL
-> title STRING(255) NOT NULL
-> slug STRING(255) NOT NULL UNIQUE
-> content TEXT NOT NULL
-> metaDescription STRING(255) NULL
-> createdAt DATETIME_IMMUTABLE NOT NULL
-> updatedAt DATETIME_IMMUTABLE NOT NULL
-> isPublished BOOLEAN NOT NULL
-> featuredImage STRING(255) NULL
-> author MANY_TO_ONE → User NOT NULL
-> category MANY_TO_ONE → Category NOT NULL
-> tags MANY_TO_MANY → Tag
-> comments ONE_TO_MANY → Commentaire

Commentaire :
-> id INT NOT NULL
-> content TEXT NOT NULL
-> createdAt DATETIME_IMMUTABLE NOT NULL
-> isApproved BOOLEAN NOT NULL
-> article MANY_TO_ONE → Article NOT NULL
-> user MANY_TO_ONE → User NULLABLE
-> authorName STRING(255) NULL
-> authorEmail STRING(255) NULL

Galerie :
-> id INT NOT NULL
-> title STRING(255) NOT NULL
-> description TEXT NULL
-> createdAt DATETIME_IMMUTABLE NOT NULL
-> updatedAt DATETIME_IMMUTABLE NOT NULL
-> images ONE_TO_MANY → Image (cascade persist/remove, orphanRemoval yes)

Image :
-> id INT NOT NULL
-> filename STRING(255) NOT NULL
-> caption STRING(255) NULL
-> createdAt DATETIME_IMMUTABLE NOT NULL
-> galerie MANY_TO_ONE → Galerie NOT NULL

7. php bin/console make:migration 
8. php bin/console doctrine:migrations:migrate
