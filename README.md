PREPARAR ENTORNO DE DESARROLLO:
(se siguen estos pasos: https://laravel.com/docs/8.x/homestead#first-steps)
1. Descargar e instalar:
	- Git (versión instalada: 2.33.1)
	- VirtualBox (versión instalada: 6.1.28)
	- Vagrant (versión instalada: 2.2.18) --> requiere reiniciar
	- PHPStorm --> licencia de estudiante UPM, jeje...

2. Abrir terminal (Git Bash en Windows):
	$ vagrant --version			// comprueba la versión de vagrant.
	$ vagrant box add laravel/homestead	// + seleccionar el provider "3" (virtualbox)

3. Instalar Homestead (clonando el repo de git) y acceder a su rama "release":
	$ git clone https://github.com/laravel/homestead.git ~/Homestead	// En Windows sin Git Bash, hay que usar "$ pwd" para ver la ruta "~"; será algo parcido a "C:/Users/Usuario"
	$ cd ~/Homestead
	$ git checkout release

4. Crear "Homestead.yaml".
	- En Linux/macOS: $ bash init.sh	// También Windows con Git Bash
	- En Windows: $ init.bat

5. Editar "Homestead.yaml" (se ha creado en la carpeta "~/Homestead"):
	A) "ip": será útil luego para modificar el fichero "hosts"
	B) "provider": asegurarse de que pone "virtualbox"
	C) "name": opcional, se puede poner un nombre a la MV. Por ejemplo, "autocrypto"
	C) "authorize" y "keys":
		- Hay que generar llaves de acceso. En la terminal: $ ssh-keygen -t rsa -C <tu@email>
	D) "folders":
		- "map": es la ruta de la carpeta en la máquina host (la de fuera de la MV) donde tendremos el proyecto. Por ejemplo, en Windows puede ser: "F:\projects\autocrypto".
		- "to": la ruta dentro de la MV. Por ejemplo, "/home/vagrant/projects/autocrypto"
	E) "sites":
		- "map": es el dominio que usarmos para acceder al proyecto a través del navegador web. Por ejemplo, "autocrypto.local". También tendremos que añadir el hostname en el fichero "hosts" (Linux/macOS: "/etc/hosts/"; Windows: "C:\Windows\System32\drivers\etc\hosts"): añadimos la línea "<ip-address-from-Homestead.yaml> <desired-hostname>", por ejemplo "192.168.10.10 autocrypto.local"
		- "to": dónde apunta el dominio, en la MV. Por ejemplo, "/home/vagrant/projects/autocrypto/public" (la carpeta "public" es la única que Symfony expone públicamente al exterior).
		- type: "symfony4"
		- php: "8.0"

	F) "databases": le cambiamos el nombre por "autocrypto". Independientemente del nombre, el usuario será "homestead" y la contraseña "secret".

	G) backup: true --> en el caso de hacer "vagrant destroy", se generará un backup de la BD indicada en F).

	H) "features":
		- mysql: false
		- mariadb: true

	I) "services", "ports": se dejan tal cual de momento.

6. Levantamos la MV (como no está creada, se creará):
	$ vagrant up (mirar Nota 2 si da problemas)

7. Comprobamos el estado de la MV:
	$ vagrant status	// Debería estar "running"

8. Entramos en la MV:
	$ vagrant ssh

9. Tendremos las carpetas mapeadas ya creadas (/home/vagrant/projects/autocrypto). Como "..../projects/autocrypto" de nuestro host está vacío, también lo está en la MV. Vamos a instalar una aplicación nueva de Symfony 5 en esta carpeta del host (https://symfony.com/doc/current/setup.html):
	A) Descargar php si no lo tenemos ya: https://www.youtube.com/watch?v=U3yP7gkTxC4
	B) Descargar e instalar Composer (versión instalada: 2.1.10).
	C) En la carpeta del host "projects", ejecutamos:
		$ composer create-project symfony/website-skeleton autocrypto

10. Cuando acabe, se sincronizarán los ficheros entre host y MV, con lo que ya podremos acceder desde nuestro navegador al proyecto, escribiendo "autocrypto.local".

11. Ejecutra "composer install" para instalar aquellas librerías que no vengan por defecto en la máquina.

12. Conectar a base de datos:
	- Lo primero es saber que homestead ya nos creó la base de datos, con los parámetros que definimos en Homestead.yaml (nos saltamos el comando php bin/console doctrine:database:create)
	- Desde PHPStorm, en la pestaña 'Database', tenemos que dar a '+' > 'Data source' > 'MariaDB'. Ahí rellenamos los campos como sigue:
		- Name: autocrypto.local
		- Host: 192.168.10.10 (la ip que teníamos en el Homestead.yaml)
		- Authentication: 'User & password'
		- User: homestead
		- Save: forever
		- Password: secret
		- Database: autocrypto
	- Instalamos los drivers (aparecerá un mensaje) y testeamos la conexión. Damos a 'Apply'.

13. Ejecutar las migraciones: "php bin/console doctrine:migrations:migrate"




Nota 1: Si se modifica "Homestead.yaml", hay que hacer "vagrant reload --provision"
Nota 2: Posibles errores en "vagrant up":
	- VERR_NEM_VM_CREATE_FAILED: https://forums.virtualbox.org/viewtopic.php?f=25&t=97412




    //////////////////////////
   // INICIAR EL DIA A DIA //
  //////////////////////////
1. Abrir PHPStorm
2. Abrir dos terminales:
	a) Host: ir a "F:\projects\autocryto"
	b) VM: ir a "C:\Users\Usuario\Homestead", y ejecutar "vagrant up && vagrant ssh". Dentro de la MV, "cd projects/autocrypto/"
3. En el navegador, entrar en http://autocrypto.local




    ///////////////////////////
   // SI NO FUNCIONA XDEBUG //
  ///////////////////////////
1. En la MV, abrir 'sudo vim /etc/php/8.0/fpm/conf.d/20-xdebug.ini'
2. Tendrá una línea de zend (zend_extension=xdebug.so). Añadir debajo las siguientes líneas:
	xdebug.client_host = 192.168.10.10  <--- Primero, probar sin esta línea.
	xdebug.mode=debug
	xdebug.start_with_request=yes
	xdebug.discover_client_host = 1
3. Probar a lanzar un comando con 'xphp bin/console <comando>'.
4. Debería salir un mensaje de configuración.
5. Si tampoco se arregla, entrar en 'Settings' > 'PHP' > 'Servers' y poner los mappings a la raiz del proyecto y a la carpeta 'bin'. En mi caso:
	- F:\projects\autocrypto        --->   /home/vagrant/projects/autocrypto
	- F:\projects\autocrypto\bin    --->   /home/vagrant/projects/autocrypto/bin
6. Si funcionan los breakpoints pero sale un error en consola, probar a quitar/poner algunas de las líneas de arriba, así como a llamar con 'php' y 'xphp'.



    /////////////////////////
   // GESTIÓN DE PAQUETES //
  /////////////////////////
Se realiza con Composer (https://getcomposer.org/doc/01-basic-usage.md), desde la máquina host.
1. Se añaden las dependencias a "composer.json". Esto puede ser manual o usando "composer require ..."
2. Cuando se quieren bloquear las dependencias, se hace "composer update". Esto las bloquea en un archivo nuevo llamado "composer.lock", que deberá guardarse en git. Para bloquear y actualizar una nueva dependencia, se hará: "composer update <package/name>".
3. Cuando alguien nuevo quiera trabajar en el proyecto, deberá ejecutar "composer install", que instalará las dependencias que describa "composer.lock". Esto también habrá que hacerlo en la MV.
4. Paquetes instalados por mí:
	- composer require symfony/serializer-pack
	- composer require symfony/maker-bundle --dev  (para usar luego: )
		- php bin/console list make --> lista todos los comandos
		- php bin/console make:controller --> crea un controlador
	- composer require annotations
	- composer require ramsey/uuid
	- composer require --dev mockery/mockery
	- ext-curl
	- ext-zip



    //////////////////////////////////////
   // CONFIGURAR EL REMOTE INTERPRETER //
  //////////////////////////////////////
No podremos ejecutar los tests hasta que no lo configuremos. Se hace siguiendo estos pasos:
https://www.jetbrains.com/help/phpstorm/configuring-remote-interpreters.html

    //////////////////////////////
   // CONFIGURAR EL REMOTE HOST//
  //////////////////////////////
https://www.jetbrains.com/help/phpstorm/creating-a-remote-server-configuration.html
- Seleccionando SFTP
- SSH configuration: vagrant@autocrypto.local:22 <-- conectar con SSh key
- Web server url: http://autocrypto.local




    /////////////////////////////////////////
   // CREAR SSH KEY PARA ACCEDER A GITHUB //
  /////////////////////////////////////////
0. (primeros tres puntos) https://kbroman.org/github_tutorial/pages/first_time.html
1. https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent
2. https://docs.github.com/en/authentication/connecting-to-github-with-ssh/adding-a-new-ssh-key-to-your-github-account
3. Probar que todo ha ido bien escribiendo lo siguiente en Git Bash (o similar): ssh -T git@github.com --> Nos dará la bienvenida (Hi venturarome! You've successfully authenticated, but GitHub does not provide shell access.)






Ver qué 'servicios' tenemos disponibles gracias al autowiring: php bin/console debug:autowiring
