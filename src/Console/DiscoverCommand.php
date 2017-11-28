<?php
namespace Broadlink\Console;

use Broadlink\Device\SP2;
use Broadlink\Factory\BroadlinkFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiscoverCommand extends Command
{
    protected function configure()
    {
        $this->setName('broadlink:discover')
            ->setDescription('Discovers broadlink devices in the network.')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $devices = BroadlinkFactory::discover();

        $table = new Table($output);
        $table->setHeaders(['type', 'name', 'mac', 'host', 'model', 'power']);


        foreach($devices as $device) {
            $power = false;
            if ($device instanceof SP2) {
//
                $device->getClient()->auth();
                $power = $device->Check_Power();
//                $device->Set_Power(!$power);
                $power = $device->Check_Power();
            }
            $object = [
                'type'=> $device->getDeviceType(),
                'name'=> $device->getName(),
                'mac'=> $device->getMac(),
                'host'=> $device->getHost(),
                'model'=> $device->getModel(),
                'power' => $power ? 'On' : 'Off',
            ];

            $table->addRow($object);

        }
        /** @var SP2 $dev */
//$dev = BroadlinkFactory::create('10.0.1.12', 'b4:43:0d:ee:e7:6a', 80, 10035);
//$dev = BroadlinkFactory::create('10.0.1.12', '6a:e7:ee:0d:43:b4',80, 10035);
//
//        $dev->Set_Power(true);

        $table->render();

    }
}