<?php

namespace Nuclear\Hierarchy\Support;


class FileKeeper {

    /**
     * Reads contents from given path
     *
     * @param string $path
     * @return mixed
     * @throws RuntimeException
     */
    public static function read($path)
    {
        if ( ! $file = @file_get_contents($path))
        {
            throw new \RuntimeException('File "' . $path . '" could not be read.');
        }

        return $file;
    }

    /**
     * Writes contents to given path
     *
     * @param string $path
     * @param string $contents
     * @throws RuntimeException
     */
    public static function write($path, $contents)
    {
        if ( ! @file_put_contents($path, $contents))
        {
            throw new \RuntimeException('File "' . $path . '" could not be written.');
        }
    }

    /**
     * Checks if a file or directory exists
     *
     * @param string $path
     * @return bool
     */
    public static function exists($path)
    {
        return file_exists($path);
    }

    /**
     * Create a directory
     *
     * @param string $path
     * @throws RuntimeException
     */
    public static function directory($path)
    {
        if ( ! @mkdir($path, 0777, true))
        {
            throw new \RuntimeException('Directory could not be created.');
        }
    }


    /**
     * Deletes a file
     *
     * @param string $path
     * @throws RuntimeException
     */
    public static function delete($path)
    {
        if (static::exists($path))
        {
            if ( ! static::tryDelete($path))
            {
                throw new \RuntimeException('File could not be deleted.');
            }
        }
    }

    /**
     * Try to delete a file
     *
     * @param $path
     * @return bool
     */
    protected static function tryDelete($path)
    {
        $success = true;

        try
        {
            if ( ! @unlink($path))
            {
                $success = false;
            }
        } catch (ErrorException $e)
        {
            $success = false;
        }

        return $success;
    }

}