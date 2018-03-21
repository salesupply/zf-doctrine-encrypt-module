# zf-doctrine-encrypt-module

Expands [51systems](https://github.com/51systems/doctrine-encrypt/) Doctrine encrypt repository. 

Provides a Zend Framework 3 & Doctrine 2 encryption module.

# Installation

    composer require salesupply/zf-doctrine-encrypt-module
    
# Requirements

 * PHP 7.2 or greater (must have Sodium extension enabled)
 * [Doctrine encrypt](https://github.com/51systems/doctrine-encrypt/) module by 51systems
 * [Doctrine ORM Module](https://github.com/doctrine/doctrine-orm-module/)
 
If you're on Windows, using Xampp, the PHP 7.2 installation might not automatically enable the Sodium extension. If this
the case, you'll get an error (`'This is not implemented, as it is not possible to securely wipe memory from PHP'`). 
Enable Sodium for PHP by adding this to your `php.ini` file:

    extension = C:\xampp\php\ext\php_sodium.dll

This might also be applicable ot other local installations.  

# Configuration

## Zend Framework

Make sure to add the module to you application configuration. In your `modules.config.php` make sure to include 
`ZfDoctrineEncryptModule`.

## Module

`*.dist` files are provided. Copy these (remove extension) to your application and fill in the required key/salt values. 
If these are filled in, it works out of the box using [Halite](https://github.com/paragonie/halite) for encryption. 

However, must be said, at the moment of writing this ReadMe, the Halite module contains duplicate `const` declarations,
as such, you must disable your `E_NOTICE` warnings in your PHP config :(

# Usage example

Simple, consider that you have an `Address` Entity, which under upcoming [EU GDPR regulation](https://www.eugdpr.org/)
requires parts of the address, such as the street, to be encrypted. This uses the key & salt required for the config
by default

To encrypt a street name, add `@Encrypted` like so: 

    /**
     * @var string
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     * @Encrypted
     */
    protected $street;
    
If you need make sure that encryption is done in a unique way, such as for passwords or keys, which need decryption
(e.g. in the case that you need to create a connection string for an external API), you can provide some options. 
Options provided are `spices`, `salt` and `pepper`. These must point to a property of the Entity you're encrypting, from
which then either the Salt or the Pepper or both of them are gotten and used.

    /**
     * @var string
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     * @Encrypted(spices="encryption")
     */
    protected $street;
    
The above example expects a property `relation` to be present. The value is given for the option `spices`, as such, 
the returned Entity when using `getEncryption` must implement the `SpicyInterface`.

**NOTE**: The option `spices` *may not be used* in conjunction with either `salt` or `pepper`. If you're *not* using 
`spices`, you may use both `salt` and `pepper`. 