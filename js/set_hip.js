async function get_from_ipinfo(){
    const request = await fetch("https://ipinfo.io/json?token=93445218020efd")
    const json = await request.json()

    console.log(json.ip, json.country)
}

async function listen_hip(ip){

    let field = document.getElementById('hiphash');
    field.value = ip;
    const ipinfo = await get_from_ipinfo();

}