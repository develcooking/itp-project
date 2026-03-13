<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../models/UserJobs.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Job.php';

class UserJobsModelTest extends TestCase
{
    private $conn;
    private $userJobs;
    private $testUser;
    private $testJob;

    protected function setUp(): void
    {
        $this->conn = $GLOBALS['conn'];
        $this->userJobs = new UserJobs($this->conn);

        $userPayload = PayloadHelper::createUser(['activated' => 1]);
        $user = new User($this->conn);
        $user->setUserName($userPayload['userName']);
        $user->setFirstName($userPayload['firstName']);
        $user->setLastName($userPayload['lastName']);
        $user->setEmail($userPayload['email']);
        $user->setPassword($userPayload['password']);
        $user->setRole($userPayload['role']);
        $user->setSecurityAnswer(password_hash($userPayload['securityAnswer'], PASSWORD_DEFAULT));
        $user->setActivated(1);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $user->post();
        $this->testUser = $user->getUserId();

        $jobPayload = PayloadHelper::createJob([
            'createdBy' => $this->testUser,
            'modifiedBy' => $this->testUser
        ]);

        $job = new Job($this->conn);
        $job->setJobName($jobPayload['name']);
        $job->setCreateBy($jobPayload['createdBy']);
        $job->setModifiedBy($jobPayload['modifiedBy']);
        $job->post();

        $jobs = $job->getAll();
        $this->testJob = $jobs[count($jobs) - 1]['jobId'];
    }

    public function test_It_Should_Assign_Job_To_User(): void
    {
        $result = $this->userJobs->assign($this->testUser, $this->testJob, $this->testUser);
        $this->assertTrue($result);
    }

    public function test_It_Should_Get_Jobs_For_User(): void
    {
        $this->userJobs->assign($this->testUser, $this->testJob, $this->testUser);
        $jobs = $this->userJobs->getJobsForUserByID($this->testUser);

        $this->assertIsArray($jobs);
        $this->assertContains($this->testJob, $jobs);
    }

    public function test_It_Should_Get_Users_For_Job(): void
    {
        $this->userJobs->assign($this->testUser, $this->testJob, $this->testUser);

        $users = $this->userJobs->getUsersForJobID($this->testJob);

        $this->assertIsArray($users);
        $this->assertContains($this->testUser, $users);
    }

    public function test_It_Should_Remove_Job_From_User(): void
    {
        $this->userJobs->assign($this->testUser, $this->testJob, $this->testUser);

        $jobsBefore = $this->userJobs->getJobsForUserByID($this->testUser);
        $this->assertContains($this->testJob, $jobsBefore);

        $result = $this->userJobs->remove($this->testUser, $this->testJob);
        $this->assertTrue($result);

        $jobsAfter = $this->userJobs->getJobsForUserByID($this->testUser);
        $this->assertNotContains($this->testJob, $jobsAfter);
    }

    public function test_It_Should_Remove_All_Jobs_From_User(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $jobPayload = PayloadHelper::createJob([
                'createdBy' => $this->testUser,
                'modifiedBy' => $this->testUser
            ]);
            $job = new Job($this->conn);
            $job->setJobName($jobPayload['name']);
            $job->setCreateBy($jobPayload['createdBy']);
            $job->setModifiedBy($jobPayload['modifiedBy']);
            $job->post();

            $jobs = $job->getAll();
            $jobId = $jobs[count($jobs) - 1]['jobId'];

            $this->userJobs->assign($this->testUser, $jobId, $this->testUser);
        }

        $jobsBefore = $this->userJobs->getJobsForUserByID($this->testUser);
        $this->assertGreaterThan(0, count($jobsBefore));

        $result = $this->userJobs->removeAllForUser($this->testUser);
        $this->assertTrue($result);

        $jobsAfter = $this->userJobs->getJobsForUserByID($this->testUser);
        $this->assertEmpty($jobsAfter);
    }

    public function test_It_Should_Assign_Multiple_Jobs_To_User(): void
    {
        $jobIds = [];

        for ($i = 0; $i < 3; $i++) {
            $jobPayload = PayloadHelper::createJob([
                'createdBy' => $this->testUser,
                'modifiedBy' => $this->testUser
            ]);
            $job = new Job($this->conn);
            $job->setJobName($jobPayload['name']);
            $job->setCreateBy($jobPayload['createdBy']);
            $job->setModifiedBy($jobPayload['modifiedBy']);
            $job->post();

            $jobs = $job->getAll();
            $jobId = $jobs[count($jobs) - 1]['jobId'];
            $jobIds[] = $jobId;

            $this->userJobs->assign($this->testUser, $jobId, $this->testUser);
        }

        $userJobs = $this->userJobs->getJobsForUserByID($this->testUser);

        foreach ($jobIds as $jobId) {
            $this->assertContains($jobId, $userJobs);
        }
    }

    public function test_It_Should_Assign_Job_To_Multiple_Users(): void
    {
        $userIds = [];

        for ($i = 0; $i < 3; $i++) {
            $userPayload = PayloadHelper::createUser(['activated' => 1]);
            $user = new User($this->conn);
            $user->setUserName($userPayload['userName']);
            $user->setFirstName($userPayload['firstName']);
            $user->setLastName($userPayload['lastName']);
            $user->setEmail($userPayload['email']);
            $user->setPassword($userPayload['password']);
            $user->setRole($userPayload['role']);
            $user->setSecurityAnswer(password_hash($userPayload['securityAnswer'], PASSWORD_DEFAULT));
            $user->setActivated(1);
            $user->setCreatedBy(null);
            $user->setModifiedBy(null);
            $user->post();

            $userId = $user->getUserId();
            $userIds[] = $userId;

            $this->userJobs->assign($userId, $this->testJob, $this->testUser);
        }

        $jobUsers = $this->userJobs->getUsersForJobID($this->testJob);

        foreach ($userIds as $userId) {
            $this->assertContains($userId, $jobUsers);
        }
    }

    public function test_It_Should_Prevent_Duplicate_Assignments(): void
    {
        $result1 = $this->userJobs->assign($this->testUser, $this->testJob, $this->testUser);
        $this->assertTrue($result1);

        $result2 = $this->userJobs->assign($this->testUser, $this->testJob, $this->testUser);
        $this->assertTrue($result2);

        $jobs = $this->userJobs->getJobsForUserByID($this->testUser);
        $count = array_count_values($jobs)[$this->testJob] ?? 0;
        $this->assertEquals(1, $count);
    }
}