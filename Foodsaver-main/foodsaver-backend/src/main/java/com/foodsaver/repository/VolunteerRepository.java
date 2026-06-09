package com.foodsaver.repository;

import com.foodsaver.model.Volunteer;
import java.util.Optional;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface VolunteerRepository extends MongoRepository<Volunteer, String> {
    Optional<Volunteer> findByEmail(String email);
    boolean existsByEmail(String email);
}
