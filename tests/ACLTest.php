<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../include/data.inc.php';
require_once __DIR__ . '/../include/customer.inc.php';
require_once __DIR__ . '/../include/project.inc.php';
require_once __DIR__ . '/../include/effort.inc.php';
require_once __DIR__ . '/../include/user.inc.php';

/**
 * Test class for ACL (Access Control List) functionality
 * Tests the fixes for null access values in constructors
 */
class ACLTest extends TestCase
{
    private $mockUser;
    
    protected function setUp(): void
    {
        // Create a mock user object for testing
        $this->mockUser = $this->createMock(User::class);
        $this->mockUser->method('giveValue')
                      ->willReturnMap([
                          ['id', '2'],
                          ['gids', '1,2,3']
                      ]);
        $this->mockUser->method('checkPermission')
                      ->with('admin')
                      ->willReturn(false);
    }
    
    /**
     * Test Customer constructor with null access - should not call getUserAccess()
     */
    public function testCustomerConstructorWithNullAccess()
    {
        // ACL_DEBUG: This should NOT trigger null access warnings
        $customer = new Customer($this->mockUser, []);
        
        // Verify default access is set for uninitialized objects
        $this->assertIsArray($customer->user_access);
        $this->assertFalse($customer->user_access['read']);
        $this->assertFalse($customer->user_access['write']);
        $this->assertFalse($customer->user_access['new']);
    }
    
    /**
     * Test Customer constructor with valid data - should call getUserAccess()
     */
    public function testCustomerConstructorWithValidData()
    {
        $validCustomerData = [
            'id' => '1',
            'access' => 'rwxrwxrwx',
            'customer_name' => 'Test Customer'
        ];
        
        $customer = new Customer($this->mockUser, $validCustomerData);
        
        // Should have proper access data
        $this->assertIsArray($customer->user_access);
    }
    
    /**
     * Test Project constructor with null access - should not call getUserAccess()
     */
    public function testProjectConstructorWithNullAccess()
    {
        $mockCustomer = $this->createMock(Customer::class);
        
        // ACL_DEBUG: This should NOT trigger null access warnings
        $project = new Project($mockCustomer, $this->mockUser, []);
        
        // Verify default access is set for uninitialized objects
        $this->assertIsArray($project->user_access);
        $this->assertFalse($project->user_access['read']);
        $this->assertFalse($project->user_access['write']);
        $this->assertFalse($project->user_access['new']);
    }
    
    /**
     * Test Project constructor with valid data - should call getUserAccess()
     */
    public function testProjectConstructorWithValidData()
    {
        $mockCustomer = $this->createMock(Customer::class);
        
        $validProjectData = [
            'id' => '1',
            'access' => 'rwxrwxrwx',
            'project_name' => 'Test Project'
        ];
        
        $project = new Project($mockCustomer, $this->mockUser, $validProjectData);
        
        // Should have proper access data
        $this->assertIsArray($project->user_access);
    }
    
    /**
     * Test Effort constructor with null access - should not call getUserAccess()
     */
    public function testEffortConstructorWithNullAccess()
    {
        // ACL_DEBUG: This should NOT trigger null access warnings
        $effort = new Effort([], $this->mockUser);
        
        // Verify default access is set for uninitialized objects
        $this->assertIsArray($effort->user_access);
        $this->assertFalse($effort->user_access['read']);
        $this->assertFalse($effort->user_access['write']);
        $this->assertFalse($effort->user_access['new']);
    }
    
    /**
     * Test Effort constructor with valid data - should call getUserAccess()
     */
    public function testEffortConstructorWithValidData()
    {
        $validEffortData = [
            'id' => '1',
            'access' => 'rwxrwxrwx',
            'description' => 'Test Effort'
        ];
        
        $effort = new Effort($validEffortData, $this->mockUser);
        
        // Should have proper access data
        $this->assertIsArray($effort->user_access);
    }
    
    /**
     * Test that getUserAccess() handles null access gracefully
     */
    public function testGetUserAccessWithNullAccess()
    {
        // Create a test data object that extends Data class
        $testObject = new class($this->mockUser) extends Data {
            public $user;
            public $data = [];
            
            public function __construct($user) {
                $this->user = $user;
                // Simulate object with null access
                $this->data['access'] = null;
                $this->data['id'] = null;
            }
        };
        
        // This should not throw deprecation warnings
        $access = $testObject->getUserAccess();
        
        // Should return proper access array
        $this->assertIsArray($access);
        $this->assertArrayHasKey('read', $access);
        $this->assertArrayHasKey('write', $access);
        $this->assertArrayHasKey('new', $access);
    }
}
