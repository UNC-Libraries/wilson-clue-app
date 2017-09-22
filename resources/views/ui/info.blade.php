@extends('layouts.ui', ['title' => 'Clue!'])

@section('content')
    <div class="container" style="margin-bottom: 2rem;">
        <div class="row">
            <div class="col-xs-12">
                <h1>Investigation procedures</h1>
                <h2>Investigate</h2>
                <ul>
                    <li>Each suspect has an investigation page. Click on their icon from the gameboard to access it.</li>
                    <li>For each suspect, answers can be found in the suspect’s room or area. Use the <a href="{{ route('ui.map') }}">map</a> to find their location.</li>
                    <li>After correctly completing the challenge, S.I.A. Agents will grant interrogation access.</li>
                </ul>

                <h2>Interrogate Suspects</h2>
                <ul>
                    <li>Each suspect has a prepared statement under advisement of legal council. They can offer that information; No more, no less.</li>
                    <li>Take notes. You get one meeting with each suspect. THIS INFORMATION IS VITAL FOR SOLVING THE CASE.</li>
                    <li>Some suspects are lying. Look for ways to validate their testimony. If a suspect lies about one thing, you should assume all of that suspect’s testimony is false.</li>
                </ul>

                <h2>Identify the Evidence</h2>
                <ul>
                    <li>The ghost is targeting one of our collection items. We need you to figure out which item</li>
                    <li>Use the ghost's case file to identify the item.</li>
                    <li>You can complete this phase at any point during the game</li>
                </ul>

                <h2>Put it all together</h2>
                <ul>
                    <li>After interrogating all 6 suspects and identifying the touched item, the indictment interface will appear.</li>
                    <li>Find a quite corner, analyze your findings and make an indictment</li>
                    <li>You must correctly identify:
                        <ol>
                            <li>The <strong>Ghost</strong></li>
                            <li>The location of the ghost's <strong>Portal</strong></li>
                            <li>The <strong>Item</strong> the ghost has touched</li>
                        </ol>
                    </li>
                </ul>

                <h2>BONUS! - Ghost DNA</h2>
                <p>The ghost has left ectoplasm around the library. Use your Ghost Goggles to find the DNA sequence</p>
                <ul>
                    <li>Partial sequencing awards 1 point</li>
                    <li>A full sequence awards 3 points</li>
                </ul>
            </div>
        </div>
    </div>
@stop