package com.foodsaver.repository;

import com.foodsaver.model.Admin;
import java.util.Optional;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface AdminRepository extends MongoRepository<Admin, String> {
    Optional<Admin> findByEmail(String email);
}
