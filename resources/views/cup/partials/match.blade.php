<div class="match">
        <table>
            <tbody>
                <tr>
                    <td style="width: auto;">
                        <div style="background: linear-gradient(to right, {{ $match->team1_c1 ?? '#ccc' }} 60%, {{ $match->team1_c2 ?? '#ccc' }} 40%);
                                    color: {{ $match->team1_c3 ?? '#000' }};
                                    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                                    padding: 1px; border-radius: 5px;">
                            {{ $match->team1_name ?? '---' }}
                        </div>
                    </td>
                    <td style="width: 30px; text-align: center;">
                        {{ $match->team1_score ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="width: auto;">
                        <div style="background: linear-gradient(to right, {{ $match->team2_c1 ?? '#ccc' }} 60%, {{ $match->team2_c2 ?? '#ccc' }} 40%);
                                    color: {{ $match->team2_c3 ?? '#000' }};
                                    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                                    padding: 1px; border-radius: 5px;">
                            {{ $match->team2_name ?? '---' }}
                        </div>
                    </td>
                    <td style="width: 30px; text-align: center;">
                        {{ $match->team2_score ?? '-' }}
                    </td>
                </tr>
            </tbody>
        </table>
</div>
