<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace StoragelessSession\Session;

class Data implements \JsonSerializable
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var bool
     */
    private $dataWasChanged = false;

    /**
     * @todo ensure serializable data?
     */
    private function __construct(array $data, array $metadata)
    {
        $this->data     = $data;
        $this->metadata = $metadata;
    }

    public static function fromDecodedTokenData(\stdClass $data)
    {
        return self::fromTokenData(self::convertStdClassToUsableStuff($data), []);
    }

    private static function convertStdClassToUsableStuff(\stdClass $shit)
    {
        $arrayData = [];

        foreach ($shit as $key => $property) {
            if ($property instanceof \stdClass) {
                $arrayData[$key] = self::convertStdClassToUsableStuff($property);

                continue;
            }

            $arrayData[$key] = $property;
        }

        return $arrayData;
    }

    public static function fromTokenData(array $data, array $metadata): self
    {
        return new self($data, $metadata);
    }

    public static function fromJsonString(string $jsonString)
    {
        $decoded = json_decode($jsonString);

        // @todo stronger validation here
        return new self($decoded['data'], $decoded['metadata']);
    }

    public static function newEmptySession(): self
    {
        return new self([], []);
    }

    /**
     * @todo ensure serializable data?
     * @param string $key
     * @param mixed  $value
     */
    public function set(string $key, $value)
    {
        $this->data[$key]     = $value;
        $this->dataWasChanged = true;
    }

    public function get(string $key)
    {
        if (! $this->has($key)) {
            throw new \OutOfBoundsException(sprintf('Non-existing key "%s" requested', $key));
        }

        return $this->data[$key];
    }

    public function remove(string $key)
    {
        unset($this->data[$key]);
        $this->dataWasChanged = true;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    // @TODO ArrayAccess stuff? Or Containers? (probably better to just allow plain keys)
    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return $this->data;
    }
}
