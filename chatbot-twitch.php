public function up()
{
    Schema::create('comandos', function (Blueprint $table) {
        $table->id();
        $table->string('nome')->unique();
        $table->string('resposta');
        $table->timestamps();
    });
}
use App\Models\Comando;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotTwitchController extends Controller
{
    public function handle(Request $request)
    {
        $mensagem = $request->input('mensagem');
        
        if (substr($mensagem, 0, 1) === '!') {
            $nomeComando = substr($mensagem, 1);
            $comando = Comando::where('nome', $nomeComando)->first();
            
            if ($comando) {
                // Digita a resposta no chat da Twitch
                $this->enviarParaChatTwitch($comando->resposta);
            }
        }
    }

    private function enviarParaChatTwitch($mensagem)
    {
        $canal = 'NOME_DO_CANAL'; // Substituir pelo nome do canal da Twitch
        
        $url = "https://api.twitch.tv/kraken/channels/$canal/chat";
        $token = 'SEU_TOKEN_DA_TWITCH'; // Substituir pelo seu token da Twitch API
        
        $response = Http::withHeaders([
            'Client-ID' => 'SEU_CLIENT_ID', // Substitua pelo seu Client ID da Twitch API
            'Authorization' => "Bearer $token",
            'Accept' => 'application/vnd.twitchtv.v5+json'
        ])->post($url, [
            'content_type' => 'application/json',
            'message' => $mensagem
        ]);
    }
}
use App\Http\Controllers\ChatbotTwitchController;
Route::post('/twitch/chatbot', [ChatbotTwitchController::class, 'handle']);
Route::post('/twitch/adicionar-comando', [ChatbotTwitchController::class, 'adicionarComando']);

public function adicionarComando(Request $request)
{
    $this->validate($request, [
        'nome' => 'required|unique:comandos,nome',
        'resposta' => 'required'
    ]);

    Comando::create([
        'nome' => $request->input('nome'),
        'resposta' => $request->input('resposta')
    ]);

    return redirect()->back()->with('success', 'Comando adicionado com sucesso!');
}
<!DOCTYPE html>
<html>
<head>
    <title>Adicionar Comando</title>
</head>
<body>
    <form action="/twitch/adicionar-comando" method="post">
        @csrf
        <label for="nome">Nome do Comando:</label>
        <input type="text" name="nome" required>
        <br>
        <label for="resposta">Resposta do Comando:</label>
        <input type="text" name="resposta" required>
        <br>
        <button type="submit">Adicionar Comando</button>
    </form>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <title>Chatbot Twitch</title>
</head>
<body>
    <h1>Bem-vindo ao Chatbot Twitch!</h1>
    <p>Use os comandos a seguir para interagir:</p>
    <ul>
        <li>!comando1</li>
        <li>!comando2</li>
        <!-- Adicionar mais comandos aqui -->
    </ul>

    <a href="/adicionar-comando">Adicionar Novo Comando</a>
</body>
</html>
