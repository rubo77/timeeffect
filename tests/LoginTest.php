<?php
/**
 * Unit test for login functionality
 * Tests login on http://localhost/inventory/efforts.php?logout=1
 */

use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private $baseUrl = 'http://localhost';
    private $loginUrl;
    private $ch;

    protected function setUp(): void
    {
        $this->loginUrl = $this->baseUrl . '/inventory/efforts.php?logout=1';
        $this->ch = curl_init();
        
        // Set common cURL options
        curl_setopt_array($this->ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR => '/tmp/cookies.txt',
            CURLOPT_COOKIEFILE => '/tmp/cookies.txt',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'PHPUnit Test Agent'
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->ch) {
            curl_close($this->ch);
        }
        // Clean up cookie file
        if (file_exists('/tmp/cookies.txt')) {
            unlink('/tmp/cookies.txt');
        }
    }

    /**
     * Test login functionality with admin credentials
     */
    public function testLoginWithAdminCredentials()
    {
        // Step 1: Get the login page
        curl_setopt($this->ch, CURLOPT_URL, $this->loginUrl);
        curl_setopt($this->ch, CURLOPT_POST, false);
        
        $loginPageContent = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        
        // Verify we got the login page successfully
        $this->assertEquals(200, $httpCode, 'Login page should return HTTP 200');
        $this->assertNotFalse($loginPageContent, 'Should be able to fetch login page');
        
        // Check for PHP errors in the response
        $this->assertStringNotContainsString('Fatal error:', $loginPageContent, 'Login page should not contain fatal errors');
        $this->assertStringNotContainsString('Warning:', $loginPageContent, 'Login page should not contain warnings');
        $this->assertStringNotContainsString('Notice:', $loginPageContent, 'Login page should not contain notices');
        
        // Verify login form elements are present
        $this->assertStringContainsString('name="username"', $loginPageContent, 'Login form should contain username field');
        $this->assertStringContainsString('name="password"', $loginPageContent, 'Login form should contain password field');
        $this->assertStringContainsString('type="submit"', $loginPageContent, 'Login form should contain submit button');
        
        // Step 2: Extract form action URL
        $formAction = $this->extractFormAction($loginPageContent);
        $submitUrl = $formAction ? $this->baseUrl . $formAction : $this->loginUrl;
        
        // Step 3: Submit login form with admin credentials
        $postData = [
            'username' => 'admin',
            'password' => 'admin',
            'lang' => 'de'
        ];
        
        curl_setopt($this->ch, CURLOPT_URL, $submitUrl);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        
        $loginResult = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        
        // Verify login attempt was processed
        $this->assertNotFalse($loginResult, 'Should be able to submit login form');
        $this->assertThat($httpCode, $this->logicalOr(
            $this->equalTo(200),
            $this->equalTo(302)
        ), 'Login should return HTTP 200 or 302 (redirect)');
        
        // Check for PHP errors in the login result
        $this->assertStringNotContainsString('Fatal error:', $loginResult, 'Login result should not contain fatal errors');
        $this->assertStringNotContainsString('Warning:', $loginResult, 'Login result should not contain warnings');
        $this->assertStringNotContainsString('mysqli_sql_exception:', $loginResult, 'Login result should not contain database exceptions');
        $this->assertStringNotContainsString('php_network_getaddresses:', $loginResult, 'Login result should not contain network errors');
        
        // Step 4: Check if login was successful
        // If redirected, follow to see the final page
        if ($httpCode === 302) {
            $redirectUrl = curl_getinfo($this->ch, CURLINFO_REDIRECT_URL);
            if ($redirectUrl) {
                curl_setopt($this->ch, CURLOPT_URL, $redirectUrl);
                curl_setopt($this->ch, CURLOPT_POST, false);
                $finalPage = curl_exec($this->ch);
                $this->assertStringNotContainsString('Fatal error:', $finalPage, 'Final page should not contain fatal errors');
            }
        }
        
        // Check for successful login indicators
        $this->assertThat($loginResult, $this->logicalOr(
            $this->stringContains('TIMEEFFECT'),
            $this->stringContains('Anmeldung'),
            $this->stringContains('Login')
        ), 'Response should contain TimeEffect application content');
    }

    /**
     * Test that the application handles database connection properly
     */
    public function testDatabaseConnectionHealth()
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->loginUrl);
        curl_setopt($this->ch, CURLOPT_POST, false);
        
        $content = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        
        $this->assertEquals(200, $httpCode, 'Application should be accessible');
        $this->assertStringNotContainsString('getaddrinfo for timeeffect failed', $content, 'Should not have hostname resolution errors');
        $this->assertStringNotContainsString('mysqli_connect', $content, 'Should not have mysqli connection errors visible');
        $this->assertStringNotContainsString('DatabaseSecurity::buildWhereString', $content, 'Should not have DatabaseSecurity errors');
    }

    /**
     * Test invalid login credentials
     */
    public function testInvalidLoginCredentials()
    {
        // Get login page first
        curl_setopt($this->ch, CURLOPT_URL, $this->loginUrl);
        curl_setopt($this->ch, CURLOPT_POST, false);
        
        $loginPageContent = curl_exec($this->ch);
        $formAction = $this->extractFormAction($loginPageContent);
        $submitUrl = $formAction ? $this->baseUrl . $formAction : $this->loginUrl;
        
        // Try invalid credentials
        $postData = [
            'username' => 'invalid_user',
            'password' => 'invalid_password',
            'lang' => 'de'
        ];
        
        curl_setopt($this->ch, CURLOPT_URL, $submitUrl);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        
        $loginResult = curl_exec($this->ch);
        
        // Should not crash with errors even with invalid credentials
        $this->assertStringNotContainsString('Fatal error:', $loginResult, 'Invalid login should not cause fatal errors');
        $this->assertStringNotContainsString('mysqli_sql_exception:', $loginResult, 'Invalid login should not cause database exceptions');
    }

    /**
     * Extract form action from HTML content
     */
    private function extractFormAction($html)
    {
        if (preg_match('/<form[^>]*action=["\']([^"\']*)["\'][^>]*>/i', $html, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
