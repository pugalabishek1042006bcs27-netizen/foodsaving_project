package com.foodsaver.repository;

import com.foodsaver.model.Donor;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;

public interface DonorRepository extends JpaRepository<Donor, Long> {
    Optional<Donor> findByEmail(String email);
    boolean existsByEmail(String email);
}
