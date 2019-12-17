<?php
/**
 * JsonSerializer
 *
 * @author Akim Maksimov <a.maksimov@artox.com>
 */
declare(strict_types=1);

namespace ArtoxLab\DStore\Serializers;

use ArtoxLab\DStore\Interfaces\SerializerInterface;

class JsonSerializer implements SerializerInterface
{
    /**
     * Json encode options
     *
     * @var int
     */
    protected $options;

    /**
     * JsonSerializer constructor.
     *
     * @param int $options Json encode options
     */
    public function __construct(
        int $options = (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION)
    ) {
        $this->options = $options;
    }

    /**
     * Serialize data
     *
     * @param mixed $data Data
     *
     * @return string
     */
    public function serialize($data): string
    {
        return json_encode($data, $this->options);
    }

    /**
     * Unserialize data
     *
     * @param string $data Data
     *
     * @return array
     */
    public function deserialize(string $data): array
    {
        return (json_decode($data, true) ?? []);
    }

}
