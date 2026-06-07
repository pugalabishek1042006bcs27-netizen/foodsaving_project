package com.foodsaver.repository;

import com.foodsaver.model.VolunteerLocation;
import java.util.List;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface VolunteerLocationRepository
    extends MongoRepository<VolunteerLocation, String>
{
    List<VolunteerLocation> findByVolunteerId(String volunteerId);
}
