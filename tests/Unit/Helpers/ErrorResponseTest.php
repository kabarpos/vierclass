<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ErrorResponse;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ErrorResponseTest extends TestCase
{
    public function test_create_returns_json_response_with_correct_structure(): void
    {
        // Act
        $response = ErrorResponse::json('Test error message', 400);
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Test error message', $data['message']);
        $this->assertArrayNotHasKey('data', $data);
        $this->assertArrayNotHasKey('error_code', $data);
    }

    public function test_create_with_custom_status_code(): void
    {
        // Act
        $response = ErrorResponse::json('Unauthorized', 401);
        
        // Assert
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Unauthorized', $data['message']);
    }

    public function test_create_with_error_code(): void
    {
        // Act
        $response = ErrorResponse::json('Validation failed', 422, 'VALIDATION_ERROR');
        
        // Assert
        $this->assertEquals(422, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Validation failed', $data['message']);
        $this->assertEquals('VALIDATION_ERROR', $data['error_code']);
    }

    public function test_create_with_additional_data(): void
    {
        // Arrange
        $additionalData = ['field' => 'email', 'value' => 'invalid-email'];
        
        // Act
        $response = ErrorResponse::json('Invalid email', 400, null, $additionalData);
        
        // Assert
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Invalid email', $data['message']);
        $this->assertEquals($additionalData, $data['data']);
    }

    public function test_create_with_all_parameters(): void
    {
        // Arrange
        $additionalData = ['errors' => ['field1' => 'error1', 'field2' => 'error2']];
        
        // Act
        $response = ErrorResponse::json(
            'Multiple validation errors',
            422,
            'VALIDATION_FAILED',
            $additionalData
        );
        
        // Assert
        $this->assertEquals(422, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Multiple validation errors', $data['message']);
        $this->assertEquals('VALIDATION_FAILED', $data['error_code']);
        $this->assertEquals($additionalData, $data['data']);
    }

    public function test_create_with_debug_information_in_development(): void
    {
        // Arrange
        $originalEnv = config('app.env');
        config(['app.env' => 'local']);
        
        // Act
        $response = ErrorResponse::json('Debug test', 500);
        
        // Assert
        $data = $response->getData(true);
        $this->assertArrayHasKey('debug', $data);
        
        // Cleanup
        config(['app.env' => $originalEnv]);
    }

    public function test_create_without_debug_information_in_production(): void
    {
        // Arrange
        $originalEnv = config('app.env');
        $originalDebug = config('app.debug');
        config(['app.env' => 'production']);
        config(['app.debug' => false]);
        
        // Act
        $response = ErrorResponse::json('Production test', 500);
        
        // Assert
        $data = $response->getData(true);
        $this->assertArrayNotHasKey('debug', $data);
        
        // Cleanup
        config(['app.env' => $originalEnv]);
        config(['app.debug' => $originalDebug]);
    }

    public function test_create_handles_empty_message(): void
    {
        // Act
        $response = ErrorResponse::json('', 400);
        
        // Assert
        $data = $response->getData(true);
        $this->assertEquals('', $data['message']);
        $this->assertFalse($data['success']);
    }

    public function test_create_handles_null_additional_data(): void
    {
        // Act
        $response = ErrorResponse::json('Test message', 400, 'TEST_CODE', []);
        
        // Assert
        $data = $response->getData(true);
        $this->assertArrayNotHasKey('data', $data);
        $this->assertEquals('TEST_CODE', $data['error_code']);
    }

    public function test_response_headers_are_correct(): void
    {
        // Act
        $response = ErrorResponse::json('Test', 400);
        
        // Assert
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }
}