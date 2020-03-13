<?php
/*
 *
 * This file is a member of the aesonus/validator-class package
 *
 * Copyright (c) 2020 Cory Laughlin <corylcomposinger at gmail.com>
 *
 * For full copyright and license information, please refer to the LICENSE
 * file that was distributed with this software
 */

namespace Aesonus\Tests;

use Aesonus\TestLib\BaseTestCase;
use Aesonus\Validator\AbstractValidator;
use Aesonus\Validator\ValidatorClassException;
use Respect\Validation\Validator;

/**
 * Description of AbstractValidatorTest
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class AbstractValidatorTest extends BaseTestCase
{
    /**
     *
     * @var AbstractValidator
     */
    protected $testObj;

    protected function setUp(): void
    {
        $this->testObj = new class () extends AbstractValidator {
            public function getRules(): array
            {
                return [
                    'field' => Validator::alnum()->length(3, 64),
                    'numbers' => Validator::numeric(),
                ];
            }
        };
        parent::setUp();
    }

    /**
     * @test
     */
    public function validatePassesValidData()
    {
        $actual = $this->testObj->validateAll([
            'field' => 'test success',
            'numbers' => '42',
            ]);
        $this->assertTrue($actual->passed());
        $this->assertFalse($actual->failed());
    }

    /**
     * @test
     */
    public function validatorExceptionHasPassedField()
    {
        try {
            $this->testObj->validateAll([
                'field' => 'test success', 'numbers' => 'fail'
            ])->asserted();
        } catch (ValidatorClassException $ex) {
            $this->assertArrayContainsValues(['field'], $ex->getPassedFields(0));
        }
    }

    /**
     * @test
     * @dataProvider validatorFailsDataProvider
     */
    public function validatorFailsInvalidData($data)
    {
        $actual = $this->testObj->validateAll($data);
        $this->assertTrue($actual->failed());
        $this->assertFalse($actual->passed());
    }

    /**
     * @test
     * @dataProvider validatorFailsDataProvider
     */
    public function validatorThrowsExceptionOnInvalidData($data)
    {
        $this->expectException(ValidatorClassException::class);
        $actual = $this->testObj->validateAll($data);
        $this->testObj->asserted();
    }

    /**
     * @test
     * @dataProvider validatorFailsDataProvider
     */
    public function validatorErrorsHaveKeys($data)
    {
        $actual = $this->testObj->validateAll($data)->errors()[0];
        $this->assertArrayContainsValues(['field', 'numbers'], array_keys($actual));
    }

    /**
     * @test
     * @dataProvider validatorFailsDataProvider
     */
    public function validatorExceptionErrorsHaveKeys($data)
    {
        $this->testObj->validateAll($data);
        try {
            $this->testObj->asserted();
        } catch (ValidatorClassException $ex) {
            $actual = $ex->getMessages(0);
            $this->assertArrayContainsValues(['field','numbers'], array_keys($actual));
        }
    }

    /**
     * @test
     * @dataProvider validatorFailsDataProvider
     */
    public function validatorExceptionHasNoPassedFields($data)
    {
        $this->testObj->validateAll($data);
        try {
            $this->testObj->asserted();
        } catch (ValidatorClassException $ex) {
            $actual = $ex->getPassedFields(0);
            $this->assertEmpty($actual);
        }
    }

    /**
     * Data Provider
     */
    public function validatorFailsDataProvider()
    {
        return [
            [[
                'field' => 'f',
                'numbers' => 'failure',
            ]],
            [[
                'field' => 'ret$#%',
                'numbers' => 'also failure'
            ]]
        ];
    }

    /**
     * @test
     */
    public function multiRowValidationPassesValidData()
    {
        $actual = $this->testObj->validateAll([
            ['field' => 'test success', 'numbers' => 42],
            ['field' => 'also a success', 'numbers' => 69]
        ]);
        $this->assertTrue($actual->passed());
        $this->assertFalse($actual->failed());
        $this->testObj->asserted();
    }

    /**
     * @test
     */
    public function multiRowValidationFailsSomeData()
    {
        $actual = $this->testObj->validateAll([
            ['field' => 'ttest success'],
            ['field' => 'a fail^s']
        ]);
        $this->assertTrue($actual->failed());
        $this->assertFalse($actual->passed());
        $this->assertArrayHasKey(1, $actual->errors());
        try {
            $this->testObj->asserted();
        } catch (ValidatorClassException $ex) {
            $this->assertArrayHasKey(1, $ex->getMessages());
            $this->assertCount(2, $ex->getMessages(1));
        }
    }

    /**
     * @test
     */
    public function multiRowValidationExceptionHasPassedField()
    {
        $actual = $this->testObj->validateAll([
            ['field' => 'ttest success', 'numbers' => array()],
            ['field' => 'another success', 'numbers' => 'fail']
        ]);
        $this->assertTrue($actual->failed());
        $this->assertFalse($actual->passed());
        $this->assertArrayHasKey(1, $actual->errors());
        try {
            $this->testObj->asserted();
        } catch (ValidatorClassException $ex) {
            $this->assertArrayHasKey(1, $ex->getMessages());
            $this->assertCount(1, $ex->getMessages(1));
            $this->assertArrayHasKey(1, $ex->getPassedFields());
            $this->assertArrayContainsAtLeastValues(['field'], $ex->getPassedFields(1));
        }
    }
}
