<?php

namespace Smartbox\Integration\FrameworkBundle\Tests\Functional\Tools\Mapper;

use Smartbox\CoreBundle\Type\SerializableArray;
use Smartbox\Integration\FrameworkBundle\Tests\Functional\BaseTestCase;
use Smartbox\Integration\FrameworkBundle\Tools\Mapper\Mapper;
use Smartbox\Integration\FrameworkBundle\Tools\Mapper\MapperInterface;

/**
 * Class MapperTest
 * @package Smartbox\Integration\FrameworkBundle\Tests\Functional\Tools\Mapper
 *
 * @coversDefaultClass \Smartbox\Integration\FrameworkBundle\Tools\Mapper\Mapper
 */
class MapperTest extends BaseTestCase
{
    /** @var  Mapper|MapperInterface */
    protected $mapper;

    public function setUp()
    {
        parent::setUp();
        $this->mapper = $this->getContainer()->get('smartesb.util.mapper');
    }

    public function tearDown()
    {
        $this->mapper = null;
    }

    public function dataProviderForCorrectMappings()
    {
        return [
            'Test mapping with simple expression' => [
                'mappings'       => [
                    'x_to_xyz' => [
                        'x' => "obj.get('x') + 1",
                        'y' => "obj.get('x') + 2",
                        'z' => "obj.get('x') + 3",
                    ],
                ],
                'mapping_name'   => 'x_to_xyz',
                'mapped_values'  => new SerializableArray(['x' => 10]),
                'expected_value' => [
                    'x' => 11,
                    'y' => 12,
                    'z' => 13,
                ],
            ],
            'Test null values' => [
                'mappings'       => [
                    'mapping_name' => [
                        'x' => "obj.get('x') + 1",
                        'y' => "obj.get('y')",
                    ],
                ],
                'mapping_name'   => 'mapping_name',
                'mapped_values'  => new SerializableArray(['x' => 10]),
                'expected_value' => [
                    'x' => 11,
                ],
            ],
            'Test empty value' => [
                'mappings'       => [
                    'mapping_name' => [
                        'x' => "obj.get('x')",
                    ],
                ],
                'mapping_name'   => 'mapping_name',
                'mapped_values'  => [],
                'expected_value' => [],
            ],
            'Test nested data' => [
                'mappings'       => [
                    'xyz_to_x' => [
                        'x' => "obj.get('x') + obj.get('y') + obj.get('z')",
                        'origins' => "mapper.mapAll([obj.get('x'),obj.get('y'),obj.get('z')],'single_value')",
                    ],
                    'single_value' => [
                        'value' => 'obj',
                    ],
                ],
                'mapping_name'   => 'xyz_to_x',
                'mapped_values'  => new SerializableArray([
                    'x' => 10,
                    'y' => 5,
                    'z' => 1,
                ]),
                'expected_value' => [
                    'x' => 16,
                    'origins' => [
                        ['value' => 10],
                        ['value' => 5],
                        ['value' => 1],
                    ],
                ],
            ],
            'Test DateTime objects' => [
                'mappings'       => [
                    'mapping_name' => [
                        'date_0' => "mapper.formatDate('Y-m-d', obj.get('null_value'))",
                        'date_1' => "mapper.formatDate('Y-m-d H:i:s', obj.get('date'))",
                        'date_2' => "mapper.formatDate(ISO8601, obj.get('date'))",
                        'date_3' => "mapper.formatDate(ISO8601Micro, obj.get('date'))",
                    ],
                ],
                'mapping_name'   => 'mapping_name',
                'mapped_values'  => new SerializableArray([
                    'date' => \DateTime::createFromFormat(\DateTime::ISO8601, '2015-01-01T20:00:00+01:00'),
                    'null_value' => null,
                ]),
                'expected_value' => [
                    'date_1' => '2015-01-01 20:00:00',
                    'date_2' => '2015-01-01T20:00:00+0100',
                    'date_3' => '2015-01-01T20:00:00.000',
                ]
            ],
        ];
    }

    /**
     * @covers ::map
     * @covers ::addMappings
     *
     * @dataProvider dataProviderForCorrectMappings
     * @param array $mappings
     * @param $mappingName
     * @param $mappedValue
     * @param array $expectedValue
     */
    public function testMap(array $mappings, $mappingName, $mappedValue, array $expectedValue)
    {
        $this->mapper->addMappings($mappings);

        $res = $this->mapper->map($mappedValue, $mappingName);

        $this->assertEquals($expectedValue, $res);
    }

    /**
     * @covers ::map
     */
    public function testMapForEmptyMappingName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid mapping name ""');

        $this->mapper->map(
            new SerializableArray(['x' => 10]),
            ''
        );
    }

    /**
     * @covers ::map
     */
    public function testMapForNotExistingMapping()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid mapping name "this_mapping_does_not_exist"');

        $this->mapper->addMappings([
            'example_mapping' => [
                'x' => "obj.get('x')",
            ]
        ]);

        $this->mapper->map(
            new SerializableArray([
                'x' => 10,
                'y' => 5,
                'z' => 1,
            ]),
            'this_mapping_does_not_exist'
        );
    }

    public function dataProviderForMapAll()
    {
        return [
            'Test mapping with no elements to map' => [
                'mappings'       => [
                    'x_to_xyz' => [
                        'x' => "obj.get('x') + 1",
                        'y' => "obj.get('x') + 2",
                        'z' => "obj.get('x') + 3",
                    ],
                ],
                'mapping_name'   => 'x_to_xyz',
                'elements'       => [],
                'expected_value' => [],
            ],
            'Test mapping with simple expression' => [
                'mappings'       => [
                    'x_to_xyz' => [
                        'x' => "obj.get('x') + 1",
                        'y' => "obj.get('x') + 2",
                        'z' => "obj.get('x') + 3",
                    ],
                ],
                'mapping_name'   => 'x_to_xyz',
                'elements'       => [
                    'x_10' => new SerializableArray(['x' => 10]),
                    'x_11' => new SerializableArray(['x' => 11]),
                    'x_12' => new SerializableArray(['x' => 12]),
                ],
                'expected_value' => [
                    'x_10' => [
                        'x' => 11,
                        'y' => 12,
                        'z' => 13,
                    ],
                    'x_11' => [
                        'x' => 12,
                        'y' => 13,
                        'z' => 14,
                    ],
                    'x_12' => [
                        'x' => 13,
                        'y' => 14,
                        'z' => 15,
                    ],
                ],
            ]
        ];
    }

    /**
     * @covers ::mapAll
     * @covers ::map
     * @covers ::addMappings
     *
     * @dataProvider dataProviderForMapAll
     * @param array $mappings
     * @param $mappingName
     * @param array $elements
     * @param array $expectedValue
     */
    public function testMapAll(array $mappings, $mappingName, array $elements, array $expectedValue)
    {
        $this->mapper->addMappings($mappings);

        $res = $this->mapper->mapAll($elements, $mappingName);

        $this->assertEquals($expectedValue, $res);
    }
}