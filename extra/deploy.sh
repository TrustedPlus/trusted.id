#!/bin/bash

path_to_module="/var/www/"$1"/bitrix/modules/"
alredyExist=false
function checkPathExist {
	pathArr=($1)
	for i in ${pathArr[@]}
		do
			if [ ! -d "$i" ];
				then 
				echo "Path "$i" does't exist!"
				exit 1
			fi
		done
}

function checkPathUnExist {
	pathArr=($1)
	for i in ${pathArr[@]}
		do
			if [ -d "$i" ];
				then 
				alredyExist=true
			fi
		done
}


checkPathExist $path_to_module
checkPathUnExist $path_to_module/trusted.id

sudo rm -rf $path_to_module/trusted.id
sudo cp -r trusted.id $path_to_module
sudo chown -R www-data:www-data $path_to_module/trusted.id
sudo chmod 2775 $path_to_module/trusted.id
sudo find $path_to_module/trusted.id -type f -exec chmod 0664 {} \;
sudo find $path_to_module/trusted.id -type d -exec chmod 2775 {} \;

if [[ $alredyExist == true ]]; then
	echo 'WARNING: THE MODULE WAS EXIST ON HDD BEFORE DEPLOY! MAY BE OLD DATABASE ENTRY OR SOME EXTRA FILES IS PRESENT!'
fi