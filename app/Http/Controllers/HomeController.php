<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use App\Models\RepositoryLike;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    private function getRepositoriesRequest($request) {
        $url = config('app.git_api_url') . 'search/repositories?order=desc&page=1&per_page=50&q=';
        $inNameQulifier = '+in:name';
        $searchParam = ($request->has('search')) ? $request->get('search') : 'PHP';
        $url = $url . $searchParam . $inNameQulifier;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'User-Agent: ' . config('app.name'),
            'Content-Type: application/json'
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function getRepositories(Request $request){
        $response = $this->getRepositoriesRequest($request);
        $responseArr = [];
        if (isset($response['items'])) {
            $repositoriesArr = $response['items'];
            $responseArr = [];
            foreach ($repositoriesArr as $r) {
                $item = [];
                $item['name'] = $r['name'];
                $item['owner'] = $r['owner']['login'];
                $item['like_type'] = -1;
                $repository = Repository::where(['name' => $r['name'], 'owner' => $r['owner']])->with('currentUserLike')->first();
                if ($repository && $repository->currentUserLike) {
                    $item['like_type'] = $repository->currentUserLike->like;
                }
                $responseArr[] = $item;
            }
        }
        return response()->json($responseArr);
    }

    public function setLike(Request $request){
        $status = 'error';
        $post = $request->all();
        $name = (isset($post['name']) && $post['name'] != '') ? $post['name'] : '';
        $owner = (isset($post['owner']) && $post['owner'] != '') ? $post['owner'] : '';
        $type = (isset($post['type']) && $post['type'] != '') ?  ($post['type'] == 'like') ? RepositoryLike::TYPE_LIKE :  RepositoryLike::TYPE_DISLIKE : RepositoryLike::TYPE_LIKE;
        $repository = Repository::where(['name' => $name, 'owner' => $owner])->first();
        if (!$repository) {
            $repository = new Repository();
            $repository->owner = $owner;
            $repository->name = $name;
            $repository->save();
        }
        $repositoryLike = RepositoryLike::where(['repository_id' => $repository->id, 'user_id' => auth()->user()->id])->first();
        if (!$repositoryLike) {
            $repositoryLike = new RepositoryLike();
            $repositoryLike->repository_id = $repository->id;
            $repositoryLike->user_id = auth()->user()->id;
            $repositoryLike->like = $type;
            if ($repositoryLike->save())  {
                $status = 'success';
            }
        } else {
            $repositoryLike->like = $type;
            if ($repositoryLike->update()) {
                $status = 'success';
            }
        }
        return response()->json(['status' => $status]);
    }

    private function getRepositoryRequest($owner, $name) {
        $url = config('app.git_api_url') . "repos/$owner/$name";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'User-Agent: ' . config('app.name'),
            'Content-Type: application/json'
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }

    public function showRepository($owner, $name){
        $repository = $this->getRepositoryRequest($owner, $name);
        if (isset($response['message']) && $response['message'] == 'Not Found') {
            abort(404);
        }
        $repositoryInfo = ['name' => '', 'owner' => '', 'likes' => 0, 'dislikes' => 0, 'is_liked' => false];
        if (isset($repository['name']) && $repository['name'] != '') $repositoryInfo['name'] = $repository['name'];
        if (isset($repository['owner']) && isset($repository['owner']['login']) && $repository['owner']['login'] != '') $repositoryInfo['owner'] = $repository['owner']['login'];
        $dbRepository = Repository::where(['name' => $name, 'owner' => $owner])->withCount(['likes', 'dislikes'])->with('currentUserLike')->first();
        if ($dbRepository) {
            if($dbRepository->likes_count) {
                $repositoryInfo['likes'] = $dbRepository->likes_count;
            }
            if ($dbRepository->dislikes_count) {
                $repositoryInfo['dislikes'] = $dbRepository->dislikes_count;
            }
            if ($dbRepository->currentUserLike && $dbRepository->currentUserLike->like == RepositoryLike::TYPE_LIKE) {
                $repositoryInfo['is_liked'] = true;
            }
        }
        return view('repository', [ 'repositoryInfo' => $repositoryInfo]);
    }
}
