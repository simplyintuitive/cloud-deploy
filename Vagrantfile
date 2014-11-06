# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

	config.vm.define :web do |web|
		web.vm.box = "UbuntuTrusty14.04-AMD64"

		# URL to base box image
		web.vm.box_url = "https://s3-ap-southeast-2.amazonaws.com/simplyintuitive-vagrant/UbuntuTrusty14.04-AMD64.box"

		web.vm.network :private_network, ip: "192.168.11.15"

		web.vm.provider "virtualbox" do |v|
			v.customize ["modifyvm", :id, "--memory", 1524]
		end

		# Run the bootstrap shell script to install things we need
		web.vm.provision "shell", path: "vagrant/bootstrap.sh", run: "always"


		web.vm.synced_folder ".", "/vagrant", :nfs => true
	end

	if Vagrant.has_plugin?("vagrant-proxyconf")
		config.proxy.http     = "http://bc-che-mac.ccla.com.au:3128/"
		config.proxy.https    = "http://bc-che-mac.ccla.com.au:3128/"
		config.proxy.no_proxy = "localhost,127.0.0.1"
	end

end
