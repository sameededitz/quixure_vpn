<?php

namespace App\Livewire;

use App\Models\Server;
use Livewire\Attributes\Validate;
use Livewire\Component;

class SubServerAdd extends Component
{
    public $server;

    #[Validate]
    public $name;

    #[Validate]
    public $ip_address;

    #[Validate]
    public $ovpn_user;

    #[Validate]
    public $ovpn_password;

    #[Validate]
    public $wg_panel_address;

    #[Validate]
    public $wg_panel_password;

    #[Validate]
    public $ipsec_user;

    #[Validate]
    public $ipsec_password;

    #[Validate]
    public $ipsec_psk;

    #[Validate]
    public $ipsec_server;

    #[Validate]
    public $ovpn_config;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|string|max:255',
            'ovpn_user' => 'required|string|max:255',
            'ovpn_password' => 'required|string|max:255',
            'wg_panel_address' => 'required|string|max:255',
            'wg_panel_password' => 'required|string|max:255',
            'ovpn_config' => 'required|string|max:9999',
            'ipsec_user' => 'required|string',
            'ipsec_password' => 'required|string',
            'ipsec_psk' => 'required|string',
            'ipsec_server' => 'required|string',
        ];
    }

    public function mount(Server $server)
    {
        $this->server = $server;
    }

    public function submit()
    {
        $this->validate();
        $this->server->subServers()->create([
            'name' => $this->name,
            'ip_address' => $this->ip_address,
            'ovpn_user' => $this->ovpn_user,
            'ovpn_password' => $this->ovpn_password,
            'wg_panel_address' => $this->wg_panel_address,
            'wg_panel_password' => $this->wg_panel_password,
            'ipsec_user' => $this->ipsec_user,
            'ipsec_password' => $this->ipsec_password,
            'ipsec_psk' => $this->ipsec_psk,
            'ipsec_server' => $this->ipsec_server,
            'ovpn_config' => $this->ovpn_config,
        ]);

        return redirect()->route('all-sub-servers', $this->server->id)->with([
            'status' => 'success',
            'message' => 'Sub Server Added Successfully',
        ]);
    }
    public function render()
    {
        return view('livewire.sub-server-add');
    }
}
