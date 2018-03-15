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

`*.dist` files are provided. Copy these (remove extension) to your application and fill in the required key/salt values. 
If these are filled in, it works out of the box using [Halite](https://github.com/paragonie/halite) for encryption. 

However, must be said, at the moment of writing this ReadMe, the Halite module contains duplicate `const` declarations,
as such, you must disable your `E_NOTICE` warnings in your PHP config :(

# Usage example

Simple, consider that you have an `Address` Entity, which under upcoming [EU GDPR regulation](https://www.eugdpr.org/)
requires parts of the address, such as the street, to be encrypted. 

To encrypt a street name, add `@Encrypted` like so: 

    /**
     * @var string
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     * @Encrypted
     */
    protected $street;
    
  