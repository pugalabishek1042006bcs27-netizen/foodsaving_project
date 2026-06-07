package com.foodsaver.repository;

import com.foodsaver.model.Volunteer;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;

public interface VolunteerRepository extends JpaRepository<Volunteer, Long> {
    Optional<Volunteer> findByEmail(String email);
    boolean existsByEmail(String email);
}
