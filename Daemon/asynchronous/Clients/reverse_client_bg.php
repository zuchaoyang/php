<?php
/*
 * Gearman PHP Extension
 *
 * Copyright (C) 2008 James M. Luedke <contact@jamesluedke.com>,
 *                    Eric Day <eday@oddments.org>
 * All rights reserved.
 *
 * Use and distribution licensed under the PHP license.  See
 * the LICENSE file in this directory for full text.
 */

/* create our object */
$gmclient= new GearmanClient();

/* add the default server */
$gmclient->addServer('127.0.0.1:4730');

/* run reverse client */
$job_handle = $gmclient->doBackground("reverse_string", "this is a test");

if ($gmclient->returnCode() != GEARMAN_SUCCESS)
{
  echo "bad return code\n";
  exit;
}
exit;
