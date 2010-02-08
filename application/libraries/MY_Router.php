<?php

class Router extends Router_Core
{
	public static function setup()
	{
		if ( ! empty($_SERVER['QUERY_STRING']))
		{
			// Set the query string to the current query string
			Router::$query_string = '?'.trim($_SERVER['QUERY_STRING'], '&/');
		}

		if (Router::$routes === NULL)
		{
			// Load routes
			Router::$routes = Kohana::config('routes');
		}

		// Default route status
		$default_route = FALSE;

		if (Router::$current_uri === '')
		{
			// Make sure the default route is set
			if ( ! isset(Router::$routes['_default']))
				throw new Kohana_Exception('core.no_default_route');

			// Use the default route when no segments exist
			Router::$current_uri = Router::$routes['_default'];

			// Default route is in use
			$default_route = TRUE;
		}

		// Make sure the URL is not tainted with HTML characters
		Router::$current_uri = html::specialchars(Router::$current_uri, FALSE);

		// Remove all dot-paths from the URI, they are not valid
		Router::$current_uri = preg_replace('#\.[\s./]*/#', '', Router::$current_uri);

		// At this point segments, rsegments, and current URI are all the same
		Router::$segments = Router::$rsegments = Router::$current_uri = trim(Router::$current_uri, '/');

		// Set the complete URI
		Router::$complete_uri = Router::$current_uri.Router::$query_string;

		// Explode the segments by slashes
		Router::$segments = ($default_route === TRUE OR Router::$segments === '') ? array() : explode('/', Router::$segments);

		if ($default_route === FALSE AND count(Router::$routes) > 1)
		{
			// Custom routing
			Router::$rsegments = Router::routed_uri(Router::$current_uri);
		}

		// The routed URI is now complete
		Router::$routed_uri = Router::$rsegments;

		// Routed segments will never be empty
		Router::$rsegments = explode('/', Router::$rsegments);

		// Prepare to find the controller
		$controller_path = '';
		$method_segment  = NULL;

		// Paths to search
		$paths = Kohana::include_paths();

		foreach (Router::$rsegments as $key => $segment)
		{
			// Add the segment to the search path
			$controller_path .= $segment;

			$found = FALSE;
			foreach ($paths as $dir)
			{
				// Search within controllers only
				$dir .= 'controllers/';

				if (is_dir($dir.$controller_path) OR is_file($dir.$controller_path.EXT))
				{
					// Valid path
					$found = TRUE;

					// The controller must be a file that exists with the search path
					if ($c = str_replace('\\', '/', realpath($dir.$controller_path.EXT))
					    AND is_file($c) AND strpos($c, $dir) === 0)
					{
						// Set controller name
						Router::$controller = $segment;

						// Change controller path
						Router::$controller_path = $c;

						// Set the method segment
						$method_segment = $key + 1;

						// Stop searching
						break;
					}
				}
			}

			if ($found === FALSE)
			{
				// Maximum depth has been reached, stop searching
				break;
			}

			// Add another slash
			$controller_path .= '/';
		}

		if ($method_segment !== NULL AND isset(Router::$rsegments[$method_segment]))
		{
			// Set method
			Router::$method = Router::$rsegments[$method_segment];

			if (isset(Router::$rsegments[$method_segment + 1]))
			{
				// Set arguments
				Router::$arguments = array_slice(Router::$rsegments, $method_segment + 1);
			}
		}

		// Last chance to set routing before a 404 is triggered
		Event::run('system.post_routing');

		if (Router::$controller === NULL)
		{
			// No controller was found, so no regular page can be rendered

      $paths = Kohana::include_paths();

      foreach ($paths as $dir)
      {
        // Search within controllers only
        $dir .= 'controllers/';

        if (is_dir($dir.'collection') OR is_file($dir.'collection'.EXT))
        {
          // The controller must be a file that exists with the search path
          if ($c = str_replace('\\', '/', realpath($dir.'collection'.EXT))
              AND is_file($c) AND strpos($c, $dir) === 0) {
          }
        }
      }
      Router::$controller      = 'collection';
      Router::$controller_path = $c;
      Router::$method          = 'show404';
		}

		if (Router::$controller === NULL)
		{
			// this point should never be reached
      exit;
		}
	}
}