<?php
/**
 * Json Serializer Test
 *
 * @author Denis Ptushko <d.ptushko@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\DStore\Tests\Serializers;

use ArtoxLab\DStore\Serializers\JsonSerializer;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class JsonSerializerUnitTest extends TestCase
{

    /**
     * Json Serializer
     *
     * @var JsonSerializer
     */
    protected $serializer;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->serializer = new JsonSerializer();
    }

    /**
     * Test serialize method
     *
     * @param array $data Data for serialization
     *
     * @dataProvider dataProvider
     *
     * @return void
     */
    public function testSerialize(array $data): void
    {
        $expectedData   = json_encode(
            $data,
            (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION)
        );
        $serializedData = $this->serializer->serialize($data);

        $this->assertEquals($expectedData, $serializedData);
    }

    /**
     * Test deserialize method
     *
     * @param array $data Data for deserialization
     *
     * @dataProvider dataProvider
     *
     * @return void
     */
    public function testDeserialize(array $data): void
    {
        $serializedData   = $this->serializer->serialize($data);
        $expectedData     = json_decode($serializedData, true);
        $unserializedData = $this->serializer->deserialize($serializedData);

        $this->assertEquals($expectedData, $unserializedData);
    }

    /**
     * Test if the correct data type return after serialization
     *
     * @param array $originalData Data for test
     *
     * @dataProvider dataProvider
     *
     * @return void
     */
    public function testDataTypeAfterSerialization(array $originalData): void
    {
        $serializedData   = $this->serializer->serialize($originalData);
        $unserializedData = $this->serializer->deserialize($serializedData);
        foreach ($unserializedData as $key => $item) {
            $this->assertEquals(gettype($originalData[$key]), gettype($unserializedData[$key]));
        }
    }

    /**
     * Data provider for test
     *
     * @return array
     */
    public function dataProvider()
    {
        $faker = Factory::create();

        return [
            [
                [
                    'id'     => $faker->randomDigitNotNull,
                    'title'  => $faker->word,
                    'price'  => $faker->randomFloat(),
                    'active' => $faker->boolean,
                    'brand'  => null,
                ],
            ],
            [
                [
                    'id'     => 1,
                    'title'  => (string) 1,
                    'price'  => (float) 1,
                    'active' => (bool) 1,
                    'brand'  => null,
                ],
            ],
        ];
    }

}
