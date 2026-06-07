package com.foodsaver.repository;

import com.foodsaver.model.Donor;
import java.util.Optional;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface DonorRepository extends MongoRepository<Donor, String> {
    Optional<Donor> findByEmail(String email);
    boolean existsByEmail(String email);
}
