<?php

namespace App\Interfaces;

interface UserServiceInterface
{
    /**
     * Create a new user
     */
    public function createUser($data);

    /**
     * Update user
     */
    public function updateUser($userId, $data);

    /**
     * Delete user
     */
    public function deleteUser($userId);

    /**
     * Get all users
     */
    public function getAllUsers();

    /**
     * Get users by role
     */
    public function getUsersByRole($role);

    /**
     * Get students by year and section
     */
    public function getStudentsByYearSection($yearLevel, $section);

    /**
     * Sort users by role hierarchy and additional criteria
     */
    public function sortUsers(array $users): array;

    /**
     * Get users sorted by role and other criteria
     */
    public function getAllUsersSorted(): array;

    /**
     * Prepare user data for view presentation
     */
    public function prepareUsersForView(array $users): array;
}