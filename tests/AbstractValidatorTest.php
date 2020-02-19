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
        $actual = $this->testObj->validateAll(['field' => 'test success']);
        $this->assertTrue($actual->passed());
        $this->assertFalse($actual->failed());
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
        $this->assertArrayContainsValues(['field'], array_keys($actual));
    }

    /**
     * Data Provider
     */
    public function validatorFailsDataProvider()
    {
        return [
            [[
                'field' => 'f'
            ]],
            [[
                'field' => 'ret$#%'
            ]]
        ];
    }

    /**
     * @test
     */
    public function multiRowValidationPassesValidData()
    {
        $actual = $this->testObj->validateAll([
            ['field' => 'test success'],
            ['field' => 'also a success']
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
            $this->assertCount(1, $ex->getMessages(1));
        }
    }
}
