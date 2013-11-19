<?php
  namespace Fiv\Spl;

  /**
   *
   * @author Ivan Scherbak <dev@funivan.com>
   */
  abstract class Command {

    protected $args = [];

    protected $registerArguments = [];

    protected $argumentsAlias = [];

    protected $restartable = false;

    protected $stoppable = true;

    protected $allowMultiInstance = false;

    protected $isRunning = false;

    protected $initThreads = false;

    protected $threadResultFromEcho = false;

    public function __construct() {

      $this->registerArgument('help', 'Show help', 'h');
      $this->registerArgument('run', 'Ignore code suggest and run command immediately', 'r');
      $this->registerArgument('interactive', 'Run script in interactive mode. Stop ', 'i');

      if ($this->restartable) {
        $this->registerArgument('restart', 'Restart service');
      }

      if (!$this->allowMultiInstance and $this->stoppable) {
        $this->registerArgument('stop', 'Stop service');
      }

      if ($this->initThreads === true) {
        $this->registerArgument('stop-thread', "Stop thread by index.\n --stop-thread 1  - Stop thread with index 1");
        $this->registerArgument('stop-all-threads', 'Stop all threads');
        $this->registerArgument('thread', "Run thread.\n --thread 1  Run thread with index 1");
        $this->registerArgument('raw', "Output thread information directly to stdout");
      }

      $this->init();

      $this->args = $this->convertArguments();

      readline_completion_function([$this, 'showArgumentSuggest']);

      $interactiveMode = (isset($this->args['interactive']) or isset($this->args['i']));
      if ($interactiveMode) {
        while (true) {
          $argsFromInput = readline("$ ");
          readline_add_history($argsFromInput);
          $this->args = $this->convertArguments($argsFromInput);
          $this->args['interactive'] = true;
          $this->execute();
        }
        die();
      }

    }

    protected function init() {

    }

    protected function showArgumentSuggest($input, $index) {
      $fullCommands = [];
      $shortCommands = [];
      foreach ($this->registerArguments as $command => $info) {
        if (!empty($info['short'])) {
          $shortCommands[] = '-' . $info['short'] . ' ';
        }
        $fullCommands[] = '--' . $command . ' ';
      }
      if ($input == '-' and !empty($shortCommands)) {
        return $shortCommands;
      } else {
        return $fullCommands;
      }
    }

    protected function registerArgument($fullName, $description, $shortName = '') {
      $this->registerArguments[$fullName] = [
        'description' => $description,
        'short' => $shortName,
      ];
      $this->argumentsAlias[$fullName] = $shortName;
    }

    public function hasArgument($argument) {
      if (isset($this->args[$argument])) {
        return true;
      }

      if (!empty($this->argumentsAlias[$argument])) {
        $alias = $this->argumentsAlias[$argument];
      }

      return (!empty($alias) and isset($this->args[$alias]));
    }

    protected function getArgument($argument) {
      if (isset($this->args[$argument])) {
        return $this->args[$argument];
      }

      if (!empty($this->argumentsAlias[$argument])) {
        $alias = $this->argumentsAlias[$argument];
        if (isset($this->args[$alias])) {
          return isset($this->args[$alias]);
        }
      }

      return null;
    }


    public function convertArguments($args = '') {

      if (!empty($args) and is_string($args)) {
        $argumentsArray = explode(" ", $args);
      } else {
        $argumentsArray = $_SERVER['argv'];
        unset($argumentsArray[0]);
      }

      $argumentsArray = !empty($argumentsArray) ? $argumentsArray : array();
      $arguments = [];
      $oneKey = '-([a-zA-Z]+)'; # -r -B -C
      $multipleKeys = '-([a-zA-Z]{2,})'; # -rfsF -ac -dfLOld
      $longKeys = '--([a-zA-Z-]{2,})'; # --file --test
      $argumentsArray = array_values($argumentsArray);
      $argumentsNum = count($argumentsArray);
      for ($i = 0; $i < $argumentsNum; $i++) {
        $name = $argumentsArray[$i];

        if (preg_match('!^' . $multipleKeys . '$!', $name, $match)) {
          $multipleParams = array_flip(str_split($match[1]));
          $multipleParams = array_map(function () {
            return true;
          }, $multipleParams);

          if (isset($argumentsArray[$i + 1]) and !preg_match('!^(' . $oneKey . '|' . $multipleKeys . '|' . $longKeys . ')$!', $argumentsArray[$i + 1], $match)) {
            # get last key
            end($multipleParams);
            $lastParamName = key($multipleParams);

            # modify last key value
            $multipleParams[$lastParamName] = $argumentsArray[$i + 1];
            $i++;
          }

          $arguments = array_merge($arguments, $multipleParams);
          continue;
        }

        if (preg_match('!^(' . $longKeys . '|' . $oneKey . ')$!i', $argumentsArray[$i], $match)) {
          $param = end($match);
          $paramValue = true;
          if (isset($argumentsArray[$i + 1]) and !preg_match('!^(' . $oneKey . '|' . $multipleKeys . '|' . $longKeys . ')$!', $argumentsArray[$i + 1], $match)) {
            $paramValue = $argumentsArray[$i + 1];
            $i++;
          }
          $paramValue = (is_string($paramValue) and empty($paramValue)) ? true : $paramValue;
          $arguments[$param] = $paramValue;
        }
      }

      $allowCommands = [];
      foreach ($this->registerArguments as $command => $info) {
        $allowCommands[$command] = true;
        if (!empty($info['short'])) {
          $allowCommands[$info['short']] = true;
        }
      }

      $arguments = array_intersect_key($arguments, $allowCommands);

      return $arguments;
    }

    public function __destruct() {
      if (!empty($this->pidFile) and is_file($this->pidFile) and $this->isRunning) {
        unlink($this->pidFile);
      }
    }

    public final function execute() {

      if (isset($this->args['h']) or isset($this->args['help'])) {
        $this->showHelp();
        die();
      }

      # check system args fot threads
      if ($this->allowMultiInstance or !$this->initThreads) {
        # threads is disabled

        $stopAllThreads = isset($this->args['stop-all-threads']);
        $stopThreadByIndex = isset($this->args['stop-thread']);
        $threadRun = isset($this->args['thread']);

        if ($stopAllThreads) {
          throw new \Exception("Can not stop all threads. Enable initThreads and allowMultiInstance properties");
        }

        if ($stopThreadByIndex) {
          throw new \Exception("Can not stop thread #" . $stopThreadByIndex . ". Enable initThreads and allowMultiInstance properties");
        }

        if ($threadRun) {
          throw new \Exception("Can not run thread #" . $this->args['thread'] . ". Enable initThreads and allowMultiInstance properties");
        }
      }

      if (isset($this->args['thread']) and !is_numeric($this->args['thread'])) {
        throw new \Exception("Thread must be integer. Prefere threads with index >= 0");
      }

      # main script validation
      if ($this->allowMultiInstance and isset($this->args['restart'])) {
        throw new \Exception("Can not restart script. Multi Instance enabled. Disable allowMultiInstance and try again");
      }

      if ($this->allowMultiInstance and isset($this->args['stop'])) {
        throw new \Exception("Can not stop script. Multi Instance enabled. Disable allowMultiInstance and try again");
      }

      if (!$this->restartable and isset($this->args['restart'])) {
        throw new \Exception("Can not restart script. Restartable option disabled. Enable restartable and try again");
      }

      if (!$this->stoppable and isset($this->args['stop'])) {
        throw new \Exception("Can not stop script. stoppable option disabled. Enable stoppable and try again");
      }

      # At this stage all is ok. Just check arguments and run
      # Stop all threads index
      if (isset($this->args['stop-all-threads'])) {
        $this->stopThread();
        //@todo check if killed
        echo "Threads stopped\n";
        die();
      }

      # Stop thread by index
      if (isset($this->args['stop-thread'])) {
        //@todo check if killed
        $this->stopThread($this->args['stop-thread']);
        echo "Thread " . $this->args['stop-thread'] . " stopped\n";
        die();
      }

      # run thread
      if (isset($this->args['thread'])) {
        $this->_runThread($this->args['thread']);
        die();
      }

      $this->pidFile = $this->getRunDir() . $this->getProcessCode();

      if (!$this->allowMultiInstance) {

        if (!is_dir($this->getRunDir())) {
          mkdir($this->getRunDir(), 0777, true);
          chmod($this->getRunDir(), 0777);
        }
        if (!is_file($this->pidFile) and isset($this->args['stop'])) {
          echo "Script doesn`t run" . "\n";
          die();
        }
        if (is_file($this->pidFile)) {
          $pid = intval(trim(file_get_contents($this->pidFile), "\t\r\n "));
          if (empty($pid)) {
            die("Bad pid in file " . $this->pidFile . "\n");
          }

          if (isset($this->args['stop'])) {
            if (!$this->checkPidRunning($pid)) {
              echo "Process doesn`t run " . $pid . "\n";
              die();
            }

            exec("kill " . $pid);

            if ($this->checkPidRunning($pid)) {
              echo "Can't stop pid " . $pid . "\n";
              die();
            }
            echo $pid . " stopped" . "\n";
            die();
          }

          if ($this->checkPidRunning($pid)) {

            if (isset($this->args['restart'])) {
              exec("kill " . $pid);
              if ($this->checkPidRunning($pid)) {
                echo "Can't stop pid " . $pid . "\n";
                die();
              }
            } elseif (!$this->hasArgument('interactive')) {
              $res = $this->hasArgument('interactive');

              echo "\n***" . __LINE__ . "***\n<pre>" . print_r($this->args, true) . "</pre>\n";
              echo "\n\n" . $res . "\n\n";

              die("Already running (pid: " . $pid . ")\n");
            }
          }
        }

        file_put_contents($this->pidFile, getmypid());
        chmod($this->pidFile, 0666);
        $this->isRunning = true;
      }

      $this->beforeRun();

      $this->run();

      $this->afterRun();
    }

    /**
     *
     * @param int $threads
     * @param int $maxThreadsIndex
     */
    protected function startThreads($threads, $maxThreadsIndex = 1) {
      $threadIndex = 0;
      $activeThreadsPid = array();

      while (($this->initThreads and $threadIndex < $maxThreadsIndex) or count($activeThreadsPid) > 0) {

        if (count($activeThreadsPid) < $threads and $this->initThreads and $threadIndex < $maxThreadsIndex) {
          $this->beforeThreadRun($threadIndex);

          $threadPid = $this->executeThread($threadIndex);
          $activeThreadsPid[$threadIndex] = $threadPid;
          $threadIndex++;
        } else {
          foreach ($activeThreadsPid as $threadPidIndex => $pid) {
            if (!$this->checkPidRunning($pid)) {
              $threadResult = $this->getThreadResult($threadPidIndex);
              $this->afterThreadRun($threadPidIndex, $threadResult);
              # thread is not available now
              unset($activeThreadsPid[$threadPidIndex]);
            }
          }
        }
      }

      $this->afterAllThreadsRun();
    }

    protected function afterAllThreadsRun() {

    }

    protected function stopThread($index = null) {
      if ($index) {
        $appendCommend = " | grep -e '--thread " . $index . "'";
      } else {
        $appendCommend = '';
      }
      $partialCommand = "ps aux | grep -e '" . $this->getCalledClassFilePath() . "' $appendCommend | grep -v grep | awk ' { print $2 }' | xargs -i ";

      $killCommand = $partialCommand . ' kill {}';
      $getAllCommand = $partialCommand . ' echo {}';
      exec($killCommand);
      return shell_exec($getAllCommand);
    }

    private function getCalledClassFilePath() {
      $object = new \ReflectionObject($this);
      return $object->getFilename();
    }

    private function executeThread($index) {
      $filePath = $this->getCalledClassFilePath();
      $command = 'nohup ' . $filePath . ' --thread ' . $index . ' > ' . $this->getThreadLogFilePath($index) . ' 2>&1 & echo $!';
      $pid = shell_exec($command);
      return $pid;
    }

    /**
     *
     * @param mixed $index
     */
    protected function _runThread($index) {
      if (!isset($this->args['raw'])) {
        ob_start();
      }
      $result = $this->runThread($index);

      if (!isset($this->args['raw'])) {
        $contents = ob_get_contents();
        ob_end_clean();
        if ($this->threadResultFromEcho) {
          echo serialize($contents);
        } else {
          echo serialize($result);
        }
      }
    }

    /**
     *
     * @param type $threadIndex
     */
    protected function runThread($threadIndex) {

    }

    protected function beforeThreadRun($threadIndex) {

    }

    protected function afterThreadRun($index, $result) {

    }

    protected function getThreadLogFilePath($threadIndex) {
      return "/tmp/" . get_called_class() . '--' . $threadIndex . '-thread.log';
    }

    private function getThreadResult($threadIndex) {
      $file = $this->getThreadLogFilePath($threadIndex);
      $threadResultData = file_get_contents($this->getThreadLogFilePath($threadIndex));
      if (!empty($threadResultData)) {
        $data = unserialize($threadResultData);
      } else {
        $data = null;
      }
      if ($data === false) {
        $data = $threadResultData;
      }
      unlink($file);
      return $data;
    }

    protected function beforeRun() {

    }

    protected abstract function run();

    protected function afterRun() {

    }

    protected function showHelp() {

      $help = [];
      $maxFirstColumnLen = 0;
      foreach ($this->registerArguments as $command => $info) {
        $commandNames = '--' . $command;
        if (!empty($info['short'])) {
          $commandNames .= ', ' . $info['short'];
        }

        $maxFirstColumnLen = strlen($commandNames) > $maxFirstColumnLen ? strlen($commandNames) : $maxFirstColumnLen;

        $help[$commandNames] = $info['description'];
      }

      $maxFirstColumnLen += 2;
      $startString = "    ";
      asort($help);
      echo "\n";
      foreach ($help as $command => $info) {
        $info = str_replace("\n", "\n" . $startString . str_repeat(" ", $maxFirstColumnLen), $info);
        if (strpos($info, "\n") !== false) {
          $info .= "\n";
        }
        echo $startString . str_pad($command, $maxFirstColumnLen, ' ', STR_PAD_RIGHT) . $info . "\n";
      }
      echo "\n";
    }

    protected function getProcessCode() {
      $reflection = new \ReflectionClass($this);
      return get_called_class() . '_' . md5($reflection->getFileName());
    }

    protected function checkPidRunning($pid) {
      $pid = intval($pid);
      if (empty($pid)) {
        throw new \Exception("Empty pid");
      }
      $result = exec('ps aux | awk \'{print $2}\' | grep "^' . intval($pid) . '$"');
      if (!empty($result)) {
        return true;
      } else {
        return false;
      }
    }

    /**
     *
     * @param int  $min
     * @param int  $max
     * @param bool $output
     */
    public static function pause($min = 0, $max = 0, $output = true) {
      $max = empty($max) ? $min : $max;
      $sleepSecondsMax = rand($min, $max);
      if ($output === false) {
        sleep($sleepSecondsMax);
      } else {
        echo "\nPause:";
        for ($sleepSeconds = 0; $sleepSeconds < $sleepSecondsMax; $sleepSeconds++) {
          echo ($sleepSecondsMax - $sleepSeconds) . '.';
          sleep(1);
        }
        echo "\n";
      }
    }

    protected function getRunDir() {

      return '/tmp/' . __NAMESPACE__ . __CLASS__;
    }
  }

