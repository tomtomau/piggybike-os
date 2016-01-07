set :application, "piggy.bike"
set :domain,      "#{application}"
set :deploy_to,   "/var/www/piggybike/"
set :app_path,    "app"
set :use_sudo,      false
set :user,          "piggybike"

set :repository,  "git@github.com:tomtomau/piggybike.git"
set :scm,         :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, or `none`

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs"]


set :model_manager, "doctrine"
# Or: `propel`

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server

set  :keep_releases,  3

# Be more verbose by uncommenting the following line
# logger.level = Logger::MAX_LEVEL

set :controllers_to_clear, ['app_dev.php']
set :copy_vendors,          true

# Symfony2 2.1
before 'symfony:composer:update', 'symfony:copy_vendors'

set :writable_dirs,       ["app/cache", "app/logs"]
set :webserver_user,      "www-data"
set :permission_method,   :acl
set :use_set_permissions, true

task :restart_nginx do
    puts "Restarting nginx";
    run "sudo service nginx restart && sudo chown -R www-data:www-data /var/www/piggybike/releases/*/app/cache && sudo chown -R www-data:www-data"
end

task :stop_workers do
    puts "Stopping piggybike worker group"
    run "sudo supervisorctl -c /etc/supervisord.conf stop piggybike:*"
end

task :start_workers do
    puts "Starting piggybike worker group"
    run "sudo supervisorctl -c /etc/supervisord.conf start piggybike:*"
end

after 'deploy:restart', 'restart_nginx'
before 'deploy:create_symlink', 'stop_workers'
after 'deploy:create_symlink', 'start_workers'
