<?php

namespace SimpleSAML\Test\Module\core\Auth\Process;

use PHPUnit\Framework\TestCase;

/**
 * Test for the core:AttributeMap filter.
 */
class AttributeMapTest extends TestCase
{
    /**
     * Helper function to run the filter with a given configuration.
     *
     * @param array $config  The filter configuration.
     * @param array $request  The request state.
     * @return array  The state array after processing.
     */
    private static function processFilter(array $config, array $request)
    {
        $filter = new \SimpleSAML\Module\core\Auth\Process\AttributeMap($config, null);
        $filter->process($request);
        return $request;
    }


    public function testBasic()
    {
        $config = [
            'attribute1' => 'attribute2',
        ];
        $request = [
            'Attributes' => [
                'attribute1' => ['value'],
            ],
        ];

        $processed = self::processFilter($config, $request);
        $result = $processed['Attributes'];
        $expected = [
            'attribute2' => ['value'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testDuplicate()
    {
        $config = [
            'attribute1' => 'attribute2',
            '%duplicate',
        ];
        $request = [
            'Attributes' => [
                'attribute1' => ['value'],
            ],
        ];

        $processed = self::processFilter($config, $request);
        $result = $processed['Attributes'];
        $expected = [
            'attribute1' => ['value'],
            'attribute2' => ['value'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMultiple()
    {
        $config = [
            'attribute1' => ['attribute2', 'attribute3'],
        ];
        $request = [
            'Attributes' => [
                'attribute1' => ['value'],
            ],
        ];

        $processed = self::processFilter($config, $request);
        $result = $processed['Attributes'];
        $expected = [
            'attribute2' => ['value'],
            'attribute3' => ['value'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMultipleDuplicate()
    {
        $config = [
            'attribute1' => ['attribute2', 'attribute3'],
            '%duplicate',
        ];
        $request = [
            'Attributes' => [
                'attribute1' => ['value'],
            ],
        ];

        $processed = self::processFilter($config, $request);
        $result = $processed['Attributes'];
        $expected = [
            'attribute1' => ['value'],
            'attribute2' => ['value'],
            'attribute3' => ['value'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testCircular()
    {
        $config = [
            'attribute1' => 'attribute1',
            'attribute2' => 'attribute2',
        ];
        $request = [
            'Attributes' => [
                'attribute1' => ['value'],
                'attribute2' => ['value'],
            ],
        ];

        $processed = self::processFilter($config, $request);
        $result = $processed['Attributes'];
        $expected = [
            'attribute1' => ['value'],
            'attribute2' => ['value'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMissingMap()
    {
        $config = [
            'attribute1' => 'attribute3',
        ];
        $request = [
            'Attributes' => [
                'attribute1' => ['value'],
                'attribute2' => ['value'],
            ],
        ];

        $processed = self::processFilter($config, $request);
        $result = $processed['Attributes'];
        $expected = [
            'attribute2' => ['value'],
            'attribute3' => ['value'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testInvalidOriginalAttributeType()
    {
        $config = [
            10 => 'attribute2',
        ];
        $request = [
            'Attributes' => [
                'attribute1' => ['value'],
            ],
        ];

        $this->setExpectedException('\Exception');
        self::processFilter($config, $request);
    }

    public function testInvalidMappedAttributeType()
    {
        $config = [
            'attribute1' => 10,
        ];
        $request = [
            'Attributes' => [
                'attribute1' => ['value'],
            ],
        ];

        $this->setExpectedException('\Exception');
        self::processFilter($config, $request);
    }

    public function testMissingMapFile()
    {
        $config = [
            'non_existant_mapfile',
        ];
        $request = [
            'Attributes' => [
                'attribute1' => ['value'],
            ],
        ];

        $this->setExpectedException('\Exception');
        self::processFilter($config, $request);
    }
}
