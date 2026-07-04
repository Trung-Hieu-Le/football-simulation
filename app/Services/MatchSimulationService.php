<?php

namespace App\Services;

class MatchSimulationService
{
    // Constants
    const SITUATIONS_PER_MINUTE = 3;
    /** Tình huống halftime, tình huống thứ 135 */
    const HALFTIME_SITUATION = self::TOTAL_SITUATIONS_FULLTIME / 2; // 135
    /** Tổng số tình huống fulltime, tình huống thứ 270 */
    const TOTAL_SITUATIONS_FULLTIME = self::SITUATIONS_PER_MINUTE * 90; // 270
    /** Tổng số tình huống extra time, tình huống thứ 90 */
    const TOTAL_SITUATIONS_EXTRATIME = self::SITUATIONS_PER_MINUTE * 30; // 90
    /** Tình huống halftime extra time, tình huống thứ 120 */
    const EXTRATIME_HALFTIME_SITUATION = self::TOTAL_SITUATIONS_FULLTIME + self::TOTAL_SITUATIONS_EXTRATIME / 2; // 315
    /** Tình huống kết thúc extra time, tình huống thứ 180 */
    const EXTRATIME_END_SITUATION = self::TOTAL_SITUATIONS_FULLTIME + self::TOTAL_SITUATIONS_EXTRATIME; // 360
    /** Tình huống bắt đầu hiệp chính 1, tình huống thứ 1 */
    const START_SITUATION = 1;
    /** Tình huống bắt đầu hiệp chính 2, tình huống thứ 136 */
    const HALFTIME_START_SITUATION = self::HALFTIME_SITUATION + 1; // 136
    /** Tình huống bắt đầu hiệp phụ 1, tình huống thứ 271 */
    const EXTRATIME_START_SITUATION = self::TOTAL_SITUATIONS_FULLTIME + 1; // 271
    /** Tình huống bắt đầu hiệp phụ 2, tình huống thứ 316 */
    const EXTRATIME_HALFTIME_START_SITUATION = self::EXTRATIME_HALFTIME_SITUATION + 1; // 316

    // Field Position Mapping
    const FIELD_POSITION_GOAL_TEAM1 = 0;
    const FIELD_POSITION_PENALTY_AREA_TEAM1 = 1;
    const FIELD_POSITION_FINAL_THIRD_TEAM1 = 2;
    const FIELD_POSITION_MIDFIELD_LOW = 3;
    const FIELD_POSITION_MIDFIELD_HIGH = 4;
    const FIELD_POSITION_MIDFIELD = 5;
    const FIELD_POSITION_MIDFIELD_LOW_TEAM2 = 6;
    const FIELD_POSITION_MIDFIELD_HIGH_TEAM2 = 7;
    const FIELD_POSITION_FINAL_THIRD_TEAM2 = 8;
    const FIELD_POSITION_PENALTY_AREA_TEAM2 = 9;
    const FIELD_POSITION_GOAL_TEAM2 = 10;
    
    // Shooting positions (có thể quyết định sút)
    const SHOOTING_POSITIONS = [0, 1, 2, 8, 9, 10];
    
    const META_BONUS = 1.08; // +8%
    const META_PENALTY = 0.92; // -8%
    
    const STAMINA_FACTOR_BASE = 0.5; // 50% tỉ lệ stamina ảnh hưởng đến stats
    const STAMINA_DIVISOR = 200;
    
    // Spec base constants
    const BASE_SHOT_CHANCE = 8;
    const BASE_FOUL_CHANCE = 7;
    const BASE_PENALTY_GOAL = 78;
    const BASE_SPECIAL_EVENT = 6;

    // Zone shot bonus (index 1..9)
    const ZONE_SHOT_BONUS = [
        1 => 30,
        2 => 18,
        3 => 8,
        4 => -5,
        5 => -20,
        6 => -5,
        7 => 8,
        8 => 18,
        9 => 30,
    ];

    // Zone progress difficulty (index 1..9)
    const ZONE_PROGRESS_DIFFICULTY = [
        1 => 2.5,
        2 => 2.0,
        3 => 1.5,
        4 => 1.2,
        5 => 1.0,
        6 => 1.2,
        7 => 1.5,
        8 => 2.0,
        9 => 2.5,
    ];

    // Stamina phase decay constants
    const HALF_1_DECAY = 0.05;
    const HALF_2_DECAY = 0.12;
    const EXTRA_1_DECAY = 0.20;
    const EXTRA_2_DECAY = 0.30;

    const FOUL_DISCIPLINE_DIVISOR = 50;
    const FOUL_CHANCE_MIN = 3;
    const FOUL_CHANCE_MAX = 12;
    const PENALTY_CHANCE = 40; // 40% chance penalty khi foul ở vị trí 1 hoặc 9
    
    const COUNTER_ATTACK_CHANCE = 15; // 15% chance counter (giảm để khó phản công hơn)
    const COUNTER_MOVE_DISTANCE = 2;
    const SPEED_THRESHOLD = 70;
    const SPEED_BONUS_CHANCE = 15; // 15% chance move +2
    const MOVE_DISTANCE_NORMAL = 1;
    const MOVE_DISTANCE_FAST = 2;
    
    const DIFFICULTY_FACTOR_MULTIPLIER = 0.3; // Tăng từ 0.1 lên 0.3 để khó tấn công hơn

    const PACE_PRESSING_MULTIPLIER = 0.5; // Pace ảnh hưởng đến pressing (cướp bóng)
    
    // Shot chances and scoring impact
    const CREATIVE_BONUS_CHANCE = 15; // 15% chance move +2
    const COUNTER_AFTER_SHOT_CHANCE = 30; // 30% chance counter after shot miss (giảm từ 40%)
    const PENALTY_GOAL_CHANCE = 70; // Penalty: 70% goal chance (fallback)
    const PENALTY_ON_TARGET_CHANCE = 80; // Penalty: 80% on target (fallback)
    const NORMAL_SHOT_GOAL_CHANCE = 30; // Shot bình thường: 30% goal chance (fallback)
    const NORMAL_SHOT_ON_TARGET_CHANCE = 40; // Shot bình thường: 40% on target (fallback)
    const SHOT_ATTACK_BONUS_MULTIPLIER = 0.3; // Attack * 0.3 for on-target chance
    const SHOT_ON_TARGET_MIN = 15;
    const SHOT_ON_TARGET_MAX = 90;
    const FREE_KICK_GOAL_CHANCE = 5; // Free kick: 5% goal chance (fallback)
    const FREE_KICK_SHOT_CHANCE = 25; // Free kick: 25% sẽ shot (giảm từ 30%)
    const FREEKICK_ON_TARGET_MIN = 5;
    const FREEKICK_ON_TARGET_MAX = 45;
    const COUNTER_DISTANCE_MIN = 1;
    const COUNTER_DISTANCE_MAX = 5;
    const FREEKICK_PASS_DISTANCE_MIN = 1;
    const FREEKICK_PASS_DISTANCE_MAX = 3;
    const COUNTER_DISTANCE_DIVISOR = 20; // Counter distance = counterPower / 20
    
    const PENALTY_ON_TARGET_MIN = 70;
    const PENALTY_ON_TARGET_MAX = 95;
    const PENALTY_ATTACK_BONUS_MULTIPLIER = 0.1;
    const PENALTY_MENTAL_BONUS_MULTIPLIER = 0.15;
    
    // Shooting decision (quyết định sút hay move ball)
    const SHOOT_DECISION_BASE_CHANCE = 8; // Base 8% theo spec
    const SHOOT_DECISION_ATTACK_MULTIPLIER = 0.18; // Attack * 0.18 theo spec
    const SHOOT_DECISION_MENTAL_MULTIPLIER = 0.10; // Mental * 0.10 theo spec
    const SHOOT_DECISION_MAX = 55;
    const SPECIAL_EVENT_LUCK_MULTIPLIER = 0.12; // Luck ảnh hưởng đến special event
    
    const FREE_KICK_ON_TARGET_CHANCE = 25; // Base free kick on target chance
    
    // Counter attack
    const COUNTER_STEAL_CHANCE = 50; // 50% cướp bóng khi shoot trượt (giảm từ 60%)
    
    /**
     * Xác định đội nào giao bóng dựa trên tình huống
     */
    public function getKickoffTeam($situation, $isExtraTime = false)
    {
        if ($isExtraTime) {
            if ($situation == self::EXTRATIME_START_SITUATION) {
                return 1; // Hiệp phụ 1: Team1 giao bóng
            } else if ($situation == self::EXTRATIME_HALFTIME_START_SITUATION) {
                return 2; // Hiệp phụ 2: Team2 giao bóng
            }
        } else {
            if ($situation == self::START_SITUATION) {
                return 1; // Hiệp 1: Team1 giao bóng
            } else if ($situation == self::HALFTIME_START_SITUATION) {
                return 2; // Hiệp 2: Team2 giao bóng
            }
        }
        return 1;
    }

    /**
     * Tính toán chỉ số đội với 10 chỉ số mới và hệ số mệt mỏi/meta
     */
    public function calculateTeamStats($team, $seasonMeta, $isSecondHalf = false)
    {
        // Compute a simple stamina/decay factor used to scale effective stats in each phase
        $phaseDecay = $isSecondHalf ? self::HALF_2_DECAY : self::HALF_1_DECAY;

        $fatigueResist = pow((($team->stamina ?? 50) / 100), 0.65);
        $staminaFactor = 1 - ($phaseDecay * (1 - $fatigueResist));

        // Use raw 10 stats (no `form` multiplier). Defaults applied if missing.
        $stats = [
            'attack' => ($team->attack ?? 50),
            'defense' => ($team->defense ?? 50),
            'control' => ($team->control ?? 50),
            'stamina' => ($team->stamina ?? 50),
            'goalkeeping' => ($team->goalkeeping ?? ($team->goalkeeper ?? 50)),
            'creative' => ($team->creative ?? 50),
            'pace' => ($team->pace ?? 50),
            'mental' => ($team->mental ?? 50),
            'discipline' => ($team->discipline ?? 50),
            'luck' => ($team->luck ?? 50),
            // helper
            'stamina_factor' => $staminaFactor,
        ];

        // Apply stamina/fatigue effect to effective stats
        foreach (['attack','defense','control','pace','creative','goalkeeping'] as $k) {
            if (isset($stats[$k])) {
                $stats[$k] = $stats[$k] * $staminaFactor;
            }
        }

        $stats = $this->applyMetaFactors($stats, $seasonMeta, $isSecondHalf);

        return $stats;
    }
    
    /**
     * Áp dụng hệ số meta
     */
    private function applyMetaFactors($stats, $seasonMeta, $isSecondHalf)
    {
        $metaBonus = self::META_BONUS;
        $metaPenalty = self::META_PENALTY;

        switch ($seasonMeta) {
            case 'possession':
                $stats['control'] *= $metaBonus;
                $stats['attack'] *= $metaPenalty;
                $stats['defense'] *= $metaPenalty;
                break;
            case 'counter':
                $stats['pace'] *= $metaBonus;
                $stats['control'] *= $metaPenalty;
                break;
            case 'pressing':
                $stats['defense'] *= $metaBonus;
                $stats['stamina_factor'] *= $metaPenalty;
                break;
            case 'tiki-taka':
                $stats['control'] *= $metaBonus;
                $stats['pace'] *= $metaPenalty;
                break;
            case 'long_ball':
                $stats['attack'] *= $metaBonus;
                $stats['control'] *= $metaPenalty;
                break;
            case 'build_up':
                $stats['control'] *= $metaBonus;
                $stats['pace'] *= $metaPenalty;
                break;
            case 'low_block':
                $stats['defense'] *= $metaBonus;
                $stats['attack'] *= $metaPenalty;
                break;
            case 'high_risk':
                $stats['attack'] *= $metaBonus;
                $stats['defense'] *= $metaPenalty;
                break;
            case 'high_line':
                $stats['defense'] *= $metaBonus;
                $stats['stamina_factor'] *= $metaPenalty;
                break;
        }

        return $stats;
    }

    /**
     * Calculate possession power per spec
     */
    private function possessionPower($stats)
    {
        return ($stats['control'] * 2.0) + ($stats['stamina'] * 0.8) + ($stats['mental'] * 0.3);
    }

    /**
     * Calculate steal power per spec
     */
    private function stealPower($stats)
    {
        return ($stats['defense'] * 1.2) + ($stats['pace'] * 0.8) + ($stats['discipline'] * 0.7);
    }

    /**
     * Should shoot decision (shotChance) per spec
     */
    private function shotDecisionChance($zone, $attack, $mental)
    {
        $zoneBonus = self::ZONE_SHOT_BONUS[$zone] ?? (($zone === self::FIELD_POSITION_GOAL_TEAM1 || $zone === self::FIELD_POSITION_GOAL_TEAM2) ? 30 : 0);
        return min(self::SHOOT_DECISION_MAX, self::SHOOT_DECISION_BASE_CHANCE + $zoneBonus + ($attack * self::SHOOT_DECISION_ATTACK_MULTIPLIER) + ($mental * self::SHOOT_DECISION_MENTAL_MULTIPLIER));
    }

    /**
     * Shot power and save power / goal chance per spec
     */
    private function shotGoalChance($attack, $mental, $goalkeeping, $keeperMental)
    {
        $shotPower = ($attack * 2.0) + ($mental * 0.8);
        $savePower = ($goalkeeping * 2.0) + ($keeperMental * 0.4);
        if (($shotPower + $savePower) == 0) return 0.0;
        return $shotPower / ($shotPower + $savePower);
    }

    /**
     * Calculate chance for special lucky events based on luck stat
     */
    private function specialEventChance($luck)
    {
        return self::BASE_SPECIAL_EVENT + (($luck ?? 50) * self::SPECIAL_EVENT_LUCK_MULTIPLIER);
    }
    
    /**
     * Simulate fulltime (90 phút)
     */
    public function simulateFullTime($team1, $team2, $seasonMeta, &$matchData)
    {
        $fieldPosition = self::FIELD_POSITION_MIDFIELD;
        $currentTeam = 1;
        
        for ($situation = self::START_SITUATION; $situation <= self::TOTAL_SITUATIONS_FULLTIME; $situation++) {
            $time = (int)ceil($situation / self::SITUATIONS_PER_MINUTE);
            
            if ($situation == self::START_SITUATION || $situation == self::HALFTIME_START_SITUATION) {
                $currentTeam = $this->getKickoffTeam($situation, false);
                $fieldPosition = self::FIELD_POSITION_MIDFIELD;
            }
            
            $isSecondHalf = $situation > self::HALFTIME_SITUATION;
            
            static $cachedStats = [];
            $halfKey = $isSecondHalf ? 'half2' : 'half1';
            if (!isset($cachedStats[$halfKey])) {
                $cachedStats[$halfKey] = [
                    'team1' => $this->calculateTeamStats($team1, $seasonMeta, $isSecondHalf),
                    'team2' => $this->calculateTeamStats($team2, $seasonMeta, $isSecondHalf),
                ];
            }
            $team1Stats = $cachedStats[$halfKey]['team1'];
            $team2Stats = $cachedStats[$halfKey]['team2'];
            
            if ($currentTeam == 1) {
                $matchData['team1_possession']++;
            } else {
                $matchData['team2_possession']++;
            }
            
            $result = $this->processSituation(
                $fieldPosition,
                $currentTeam,
                $team1Stats,
                $team2Stats,
                $team1,
                $team2,
                $time,
                $matchData
            );
            
            $fieldPosition = $result['fieldPosition'];
            $currentTeam = $result['currentTeam'];
            
            if ($result['goal']) {
                $fieldPosition = self::FIELD_POSITION_MIDFIELD;
                $currentTeam = $result['goalScoredBy'] == 1 ? 2 : 1; // Đội bị ghi bàn giao bóng
            }
        }
    }
    
    /**
     * Simulate extra time (30 phút = 90 tình huống)
     */
    public function simulateExtraTime($team1, $team2, $seasonMeta, &$matchData)
    {
        $fieldPosition = self::FIELD_POSITION_MIDFIELD;
        $currentTeam = 1;
        
        for ($situation = self::EXTRATIME_START_SITUATION; $situation <= self::EXTRATIME_END_SITUATION; $situation++) {
            $time = 90 + (int)ceil(($situation - self::EXTRATIME_START_SITUATION + 1) / self::SITUATIONS_PER_MINUTE);
            
            if ($situation == self::EXTRATIME_START_SITUATION || $situation == self::EXTRATIME_HALFTIME_SITUATION + 1) {
                $currentTeam = $this->getKickoffTeam($situation, true);
                $fieldPosition = self::FIELD_POSITION_MIDFIELD;
            }
            
            $isSecondHalf = $situation > self::EXTRATIME_HALFTIME_SITUATION;
            
            static $cachedStats = [];
            $halfKey = $isSecondHalf ? 'et_half2' : 'et_half1';
            if (!isset($cachedStats[$halfKey])) {
                $cachedStats[$halfKey] = [
                    'team1' => $this->calculateTeamStats($team1, $seasonMeta, $isSecondHalf),
                    'team2' => $this->calculateTeamStats($team2, $seasonMeta, $isSecondHalf),
                ];
            }
            $team1Stats = $cachedStats[$halfKey]['team1'];
            $team2Stats = $cachedStats[$halfKey]['team2'];
            
            if ($currentTeam == 1) {
                $matchData['team1_possession']++;
            } else {
                $matchData['team2_possession']++;
            }
            
            $result = $this->processSituation(
                $fieldPosition,
                $currentTeam,
                $team1Stats,
                $team2Stats,
                $team1,
                $team2,
                $time,
                $matchData
            );
            
            $fieldPosition = $result['fieldPosition'];
            $currentTeam = $result['currentTeam'];
            
            if ($result['goal']) {
                $fieldPosition = self::FIELD_POSITION_MIDFIELD;
                $currentTeam = $result['goalScoredBy'] == 1 ? 2 : 1; // Đội bị ghi bàn giao bóng
            }
        }
    }
    
    /**
     * Xử lý một tình huống
     */
    private function processSituation($fieldPosition, $currentTeam, $team1Stats, $team2Stats, 
                                     $team1, $team2, $time, &$matchData)
    {
        $result = [
            'fieldPosition' => $fieldPosition,
            'currentTeam' => $currentTeam,
            'goal' => false,
            'goalScoredBy' => null,
            'isPenalty' => false
        ];
        
        // Kiểm tra foul
        $foulChance = rand(1, 100);
        $defendingDiscipline = $currentTeam == 1 ? $team2Stats['discipline'] : $team1Stats['discipline'];
        
        $foulThreshold = self::BASE_FOUL_CHANCE - ($defendingDiscipline * 0.08);
        $foulThreshold = max(self::FOUL_CHANCE_MIN, min(self::FOUL_CHANCE_MAX, $foulThreshold));
        
        if ($foulChance <= $foulThreshold) {
            return $this->handleFoul($fieldPosition, $currentTeam, $team1Stats, $team2Stats, 
                                    $team1, $team2, $time, $matchData);
        }
        
        // Di chuyển bóng
        $moveResult = $this->moveBall($fieldPosition, $currentTeam, $team1Stats, $team2Stats, $team1, $team2);
        $result['fieldPosition'] = $moveResult['newPosition'];
        
        // Kiểm tra quyết định sút ở các vị trí shooting (0, 1, 2, 8, 9, 10)
        if (in_array($moveResult['newPosition'], self::SHOOTING_POSITIONS)) {
            $shootingTeam = $this->getShootingTeam($moveResult['newPosition'], $currentTeam);
            $attackingTeam = $currentTeam == 1 ? $team1Stats : $team2Stats;
            
            // Quyết định sút hay move ball theo công thức spec
            $shootDecisionChance = $this->shotDecisionChance($moveResult['newPosition'], $attackingTeam['attack'], $attackingTeam['mental']);
            
            if (rand(1, 100) <= $shootDecisionChance) {
                // Quyết định sút
                return $this->handleShot($moveResult['newPosition'], $currentTeam, $team1Stats, $team2Stats, 
                                        $team1, $team2, $time, $matchData);
            }
            // Không sút, tiếp tục move ball (đã được xử lý ở moveBall)
        }
        
        // Kiểm tra counter-attack
        if ($moveResult['counterAttack']) {
            $result['currentTeam'] = 3 - $currentTeam;
        } else {
            // Kiểm tra cướp bóng - tăng cơ hội cướp bóng để khó tấn công hơn
            $stealChance = rand(1, 100);
            $attackingControl = $currentTeam == 1 ? $team1Stats['control'] : $team2Stats['control'];
            $defendingControl = $currentTeam == 1 ? $team2Stats['control'] : $team1Stats['control'];
            
            // Điều chỉnh để tăng cơ hội cướp bóng (với stats 50-100)
            $controlRatio = ($attackingControl / ($attackingControl + $defendingControl)) * 100;
            $stealThreshold = $controlRatio - 15; // Giảm threshold để tăng cơ hội cướp bóng
            
            if ($stealChance > $stealThreshold) {
                $result['currentTeam'] = 3 - $currentTeam;
            }
        }
        
        return $result;
    }
    
    /**
     * Xác định đội đang sút dựa trên vị trí và currentTeam
     */
    private function getShootingTeam($fieldPosition, $currentTeam)
    {
        // currentTeam luôn là đội sở hữu bóng và đồng thời là đội có khả năng sút.
        return $currentTeam;
    }
    
    /**
     * Tính khoảng cách đến khung thành
     */
    private function getDistanceToGoal($fieldPosition, $currentTeam)
    {
        if ($currentTeam == 1) {
            // Team1 tấn công về phía goal Team2 (vị trí 10)
            return 10 - $fieldPosition;
        } else {
            // Team2 tấn công về phía goal Team1 (vị trí 0)
            return $fieldPosition;
        }
    }
    
    /**
     * Xử lý shot
     */
    private function handleShot($fieldPosition, $currentTeam, $team1Stats, $team2Stats, 
                               $team1, $team2, $time, &$matchData)
    {
        $shootingTeam = $this->getShootingTeam($fieldPosition, $currentTeam);
        $shootingTeamName = $shootingTeam == 1 ? $team1->name : $team2->name;
        
        // Luôn có shot
        if ($shootingTeam == 1) {
            $matchData['team1_shots']++;
        } else {
            $matchData['team2_shots']++;
        }
        
        $attackingTeam = $shootingTeam == 1 ? $team1Stats : $team2Stats;
        $defendingTeam = $shootingTeam == 1 ? $team2Stats : $team1Stats;
        
        $distanceToGoal = $this->getDistanceToGoal($fieldPosition, $currentTeam);
        
        // Gần hơn → dễ on target hơn, attack càng cao càng tăng xác suất
        $onTargetBaseChance = self::NORMAL_SHOT_ON_TARGET_CHANCE;
        $onTargetBonus = max(0, (3 - $distanceToGoal)) * 5;
        $onTargetChance = $onTargetBaseChance + $onTargetBonus + ($attackingTeam['attack'] * self::SHOT_ATTACK_BONUS_MULTIPLIER);
        $onTargetChance = min(self::SHOT_ON_TARGET_MAX, max(self::SHOT_ON_TARGET_MIN, $onTargetChance));
        
        $isOnTarget = rand(1, 100) <= $onTargetChance;
        
        $result = [
            'fieldPosition' => self::FIELD_POSITION_MIDFIELD,
            'currentTeam' => $shootingTeam == 1 ? 2 : 1, 
            'goal' => false,
            'goalScoredBy' => null,
            'isPenalty' => false
        ];
        
        if ($isOnTarget) {
            if ($shootingTeam == 1) {
                $matchData['team1_shots_on_target']++;
            } else {
                $matchData['team2_shots_on_target']++;
            }
            
            // Kiểm tra goal (attack, defense, mental) - sử dụng cộng để nổi bật chỉ số
            $goalChance = $this->shotGoalChance(
                $attackingTeam['attack'],
                $attackingTeam['mental'],
                $defendingTeam['goalkeeping'],
                $defendingTeam['mental']
            ) * 100;
            
            if (rand(1, 100) <= $goalChance) {
                if ($shootingTeam == 1) {
                    $matchData['team1_score']++;
                } else {
                    $matchData['team2_score']++;
                }
                
                $matchData['specialEvents'][] = "$time': GOAL by $shootingTeamName!";
                $result['goal'] = true;
                $result['goalScoredBy'] = $shootingTeam;
                $result['currentTeam'] = $shootingTeam == 1 ? 2 : 1;
            } else {
                // Shot trượt → tỉ lệ cao cướp bóng, phản công (không đưa về midfield)
                if (rand(1, 100) <= self::COUNTER_AFTER_SHOT_CHANCE) {
                    $result['currentTeam'] = 3 - $shootingTeam; // Đội kia cướp bóng
                    $result['fieldPosition'] = $this->handleCounterAttackAfterMiss($fieldPosition, $shootingTeam, $attackingTeam, $defendingTeam);
                }
            }
        } else {
            // Shot không on target → tỉ lệ cao cướp bóng, phản công
            if (rand(1, 100) <= self::COUNTER_STEAL_CHANCE) {
                $result['currentTeam'] = 3 - $shootingTeam;
                $result['fieldPosition'] = $this->handleCounterAttackAfterMiss($fieldPosition, $shootingTeam, $attackingTeam, $defendingTeam);
            }
        }
        
        return $result;
    }
    
    /**
     * Xử lý phản công sau khi shot trượt (tỉ lệ passing, speed, mental)
     */
    private function handleCounterAttackAfterMiss($fieldPosition, $shootingTeam, $attackingTeam, $defendingTeam)
    {
        // Phản công dựa theo công thức counter attack mới
        $counterPower = (($defendingTeam['pace'] ?? 50) * 1.8) + (($defendingTeam['attack'] ?? 50) * 0.8) + (($defendingTeam['creative'] ?? 50) * 0.5);
        $counterDistance = min(self::COUNTER_DISTANCE_MAX, max(self::COUNTER_DISTANCE_MIN, (int)($counterPower / self::COUNTER_DISTANCE_DIVISOR)));
        
        if ($shootingTeam == 1) {
            // Team1 shot trượt → Team2 phản công về phía goal Team1
            return max(self::FIELD_POSITION_GOAL_TEAM1, $fieldPosition - $counterDistance);
        } else {
            // Team2 shot trượt → Team1 phản công về phía goal Team2
            return min(self::FIELD_POSITION_GOAL_TEAM2, $fieldPosition + $counterDistance);
        }
    }
    
    /**
     * Di chuyển bóng
     */
    private function moveBall($fieldPosition, $currentTeam, $team1Stats, $team2Stats, $team1, $team2)
    {
        $attackingTeam = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingTeam = $currentTeam == 1 ? $team2Stats : $team1Stats;
        
        $buildUpPower = ($attackingTeam['control'] ?? 50) * 1.4
                        + ($attackingTeam['creative'] ?? 50) * 1.5
                        + ($attackingTeam['stamina'] ?? 50) * 0.5;
        $stopProgress = ($defendingTeam['defense'] ?? 50) * 1.8
                        + ($defendingTeam['discipline'] ?? 50) * 0.6;
        
        $zone = max(1, min(9, $fieldPosition));
        $difficultyFactor = self::ZONE_PROGRESS_DIFFICULTY[$zone] ?? 1.0;
        
        $moveChance = ($buildUpPower / ($buildUpPower + ($stopProgress * $difficultyFactor))) * 100;
        $moveChance = max(5, min(95, $moveChance));

        $result = [
            'newPosition' => $fieldPosition,
            'counterAttack' => false
        ];
        
        if (rand(1, 100) <= $moveChance) {
            $moveDistance = self::MOVE_DISTANCE_NORMAL;
            if (($attackingTeam['pace'] ?? 0) > self::SPEED_THRESHOLD && rand(1, 100) <= self::CREATIVE_BONUS_CHANCE) {
                $moveDistance = self::MOVE_DISTANCE_FAST;
            }
            
            if ($currentTeam == 1) {
                $result['newPosition'] = max(self::FIELD_POSITION_GOAL_TEAM1, $fieldPosition - $moveDistance);
            } else {
                $result['newPosition'] = min(self::FIELD_POSITION_GOAL_TEAM2, $fieldPosition + $moveDistance);
            }
        } else {
            $counterChance = ((($defendingTeam['control'] ?? 50) + (($defendingTeam['pace'] ?? 50) * self::PACE_PRESSING_MULTIPLIER) + (($defendingTeam['discipline'] ?? 50) * 0.2)) / 300) * 100;
            if (rand(1, 100) <= $counterChance) {
                $result['counterAttack'] = true;
                if ($currentTeam == 1) {
                    $result['newPosition'] = min(self::FIELD_POSITION_GOAL_TEAM2, $fieldPosition + self::COUNTER_MOVE_DISTANCE);
                } else {
                    $result['newPosition'] = max(self::FIELD_POSITION_GOAL_TEAM1, $fieldPosition - self::COUNTER_MOVE_DISTANCE);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Xử lý foul
     */
    private function handleFoul($fieldPosition, $currentTeam, $team1Stats, $team2Stats, 
                               $team1, $team2, $time, &$matchData)
    {
        $foulingTeam = $currentTeam == 1 ? 2 : 1; // Đội phòng thủ phạm lỗi
        $foulingTeamName = $foulingTeam == 1 ? $team1->name : $team2->name;
        
        if ($foulingTeam == 1) {
            $matchData['team1_fouls']++;
        } else {
            $matchData['team2_fouls']++;
        }
        
        $matchData['specialEvents'][] = "$time': Foul by $foulingTeamName";
        
        // Foul ở vị trí 1 hoặc 9 → có thể penalty
        if ($fieldPosition == self::FIELD_POSITION_PENALTY_AREA_TEAM1 || 
            $fieldPosition == self::FIELD_POSITION_PENALTY_AREA_TEAM2) {
            if (rand(1, 100) <= self::PENALTY_CHANCE) {
                return $this->handlePenalty($currentTeam, $team1Stats, $team2Stats, $team1, $team2, $time, $matchData);
            }
        }
        
        // Free kick
        return $this->handleFreeKick($fieldPosition, $currentTeam, $team1Stats, $team2Stats, $team1, $team2, $time, $matchData);
    }
    
    /**
     * Xử lý free kick
     * Tỉ lệ cao là shot, còn không thì move ball lên 1 hoặc 2
     */
    private function handleFreeKick($fieldPosition, $currentTeam, $team1Stats, $team2Stats, 
                                   $team1, $team2, $time, &$matchData)
    {
        $attackingTeam = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingTeam = $currentTeam == 1 ? $team2Stats : $team1Stats;
        
        $result = [
            'fieldPosition' => self::FIELD_POSITION_MIDFIELD,
            'currentTeam' => 3 - $currentTeam,
            'goal' => false,
            'goalScoredBy' => null,
            'isPenalty' => false
        ];
        
        if (rand(1, 100) <= self::FREE_KICK_SHOT_CHANCE) {
            $scoringTeam = $currentTeam;
            $scoringTeamName = $scoringTeam == 1 ? $team1->name : $team2->name;
            
            if ($scoringTeam == 1) {
                $matchData['team1_shots']++;
            } else {
                $matchData['team2_shots']++;
            }
            
            $onTargetChance = self::FREE_KICK_ON_TARGET_CHANCE +
                              (($attackingTeam['attack'] ?? 50) * self::SHOT_ATTACK_BONUS_MULTIPLIER) +
                              (($attackingTeam['mental'] ?? 50) * self::SHOOT_DECISION_MENTAL_MULTIPLIER);
            $onTargetChance = min(self::FREEKICK_ON_TARGET_MAX, max(self::FREEKICK_ON_TARGET_MIN, $onTargetChance));
            $isOnTarget = rand(1, 100) <= $onTargetChance;
            
            if ($isOnTarget) {
                if ($scoringTeam == 1) {
                    $matchData['team1_shots_on_target']++;
                } else {
                    $matchData['team2_shots_on_target']++;
                }
                
                $freeKickPower = (($attackingTeam['creative'] ?? 50) * 1.3)
                                 + (($attackingTeam['attack'] ?? 50) * 0.8)
                                 + (($attackingTeam['mental'] ?? 50) * 1.0);
                $savePower = (($defendingTeam['goalkeeping'] ?? 50) * 1.4)
                             + (($defendingTeam['mental'] ?? 50) * 0.9);
                $goalChance = $savePower > 0 ? $freeKickPower / ($freeKickPower + $savePower) : 0.0;
                
                if (rand(1, 100) <= $goalChance * 100) {
                    if ($scoringTeam == 1) {
                        $matchData['team1_score']++;
                    } else {
                        $matchData['team2_score']++;
                    }
                    
                    $matchData['specialEvents'][] = "$time': " . (rand(1, 100) <= $this->specialEventChance($attackingTeam['luck']) ? 'Lucky Free Kick GOAL by ' : 'Free Kick GOAL by ') . $scoringTeamName . '!';
                    $result['goal'] = true;
                    $result['goalScoredBy'] = $scoringTeam;
                    $result['currentTeam'] = $scoringTeam == 1 ? 2 : 1;
                }
            }
        } else {
            $moveDistance = rand(self::FREEKICK_PASS_DISTANCE_MIN, self::FREEKICK_PASS_DISTANCE_MAX);
            if ($currentTeam == 1) {
                $result['fieldPosition'] = max(self::FIELD_POSITION_GOAL_TEAM1, $fieldPosition - $moveDistance);
            } else {
                $result['fieldPosition'] = min(self::FIELD_POSITION_GOAL_TEAM2, $fieldPosition + $moveDistance);
            }
        }
        
        return $result;
    }
    
    /**
     * Xử lý penalty
     * 100% sẽ shot. Tỉ lệ nhỏ hơn một chút là shot_on_target, tỉ lệ nhỏ hơn là ghi bàn
     */
    private function handlePenalty($currentTeam, $team1Stats, $team2Stats, $team1, $team2, $time, &$matchData)
    {
        $attackingTeam = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingTeam = $currentTeam == 1 ? $team2Stats : $team1Stats;
        
        $scoringTeam = $currentTeam;
        $scoringTeamName = $scoringTeam == 1 ? $team1->name : $team2->name;
        
        if ($scoringTeam == 1) {
            $matchData['team1_shots']++;
        } else {
            $matchData['team2_shots']++;
        }
        
        $onTargetChance = min(self::PENALTY_ON_TARGET_MAX, max(self::PENALTY_ON_TARGET_MIN, self::BASE_SHOT_CHANCE + (($attackingTeam['attack'] ?? 50) * self::PENALTY_ATTACK_BONUS_MULTIPLIER) + (($attackingTeam['mental'] ?? 50) * self::PENALTY_MENTAL_BONUS_MULTIPLIER)));
        $isOnTarget = rand(1, 100) <= $onTargetChance;
        
        $result = [
            'fieldPosition' => self::FIELD_POSITION_MIDFIELD,
            'currentTeam' => 3 - $currentTeam,
            'goal' => false,
            'goalScoredBy' => null,
            'isPenalty' => true
        ];
        
        if ($isOnTarget) {
            if ($scoringTeam == 1) {
                $matchData['team1_shots_on_target']++;
            } else {
                $matchData['team2_shots_on_target']++;
            }
            
            $penaltyShot = (($attackingTeam['attack'] ?? 50) * 1.4) + (($attackingTeam['mental'] ?? 50) * 1.2);
            $penaltySave = (($defendingTeam['goalkeeping'] ?? 50) * 1.4) + (($defendingTeam['mental'] ?? 50) * 0.9);
            $goalChance = $penaltySave > 0 ? $penaltyShot / ($penaltyShot + $penaltySave) : 0.0;
            
            if (rand(1, 100) <= $goalChance * 100) {
                if ($scoringTeam == 1) {
                    $matchData['team1_score']++;
                } else {
                    $matchData['team2_score']++;
                }
                
                $matchData['specialEvents'][] = "$time': " . (rand(1, 100) <= $this->specialEventChance($attackingTeam['luck']) ? 'Lucky Penalty GOAL by ' : 'Penalty GOAL by ') . $scoringTeamName . '!';
                $result['goal'] = true;
                $result['goalScoredBy'] = $scoringTeam;
                $result['currentTeam'] = $scoringTeam == 1 ? 2 : 1;
            } else {
                $matchData['specialEvents'][] = "$time': Penalty saved!";
            }
        } else {
            $matchData['specialEvents'][] = "$time': Penalty saved!";
        }
        
        return $result;
    }
    
    /**
     * Simulate penalty shootout
     */
    public function simulatePenaltyShootout($team1, $team2)
    {
        $team1_penalty_score = 0;
        $team2_penalty_score = 0;
        $round = 1;
        $team1_results = [];
        $team2_results = [];
        
        // Team2 sút trước lượt đầu tiên
        for ($i = 1; $i <= 5; $i++) {
            $team2_shot = $this->takePenaltyShot($team2, $team1);
            $team2_penalty_score += $team2_shot;
            $team2_results[] = $team2_shot;
            
            $remainingShots = 5 - $i;
            if (abs($team1_penalty_score - $team2_penalty_score) > $remainingShots) {
                break;
            }
            
            $team1_shot = $this->takePenaltyShot($team1, $team2);
            $team1_penalty_score += $team1_shot;
            $team1_results[] = $team1_shot;
            
            if (abs($team1_penalty_score - $team2_penalty_score) > $remainingShots) {
                break;
            }
        }
        
        // Sudden death nếu hòa sau 5 lượt
        while ($team1_penalty_score == $team2_penalty_score) {
            $round++;
            $team2_shot = $this->takePenaltyShot($team2, $team1);
            $team2_penalty_score += $team2_shot;
            $team2_results[] = $team2_shot;
            
            $team1_shot = $this->takePenaltyShot($team1, $team2);
            $team1_penalty_score += $team1_shot;
            $team1_results[] = $team1_shot;
        }
        
        $winnerId = $team1_penalty_score > $team2_penalty_score ? $team1->id : $team2->id;
        $winnerName = $team1_penalty_score > $team2_penalty_score ? $team1->name : $team2->name;
        
        return [
            'team1_results' => json_encode($team1_results),
            'team2_results' => json_encode($team2_results),
            'team1_score' => $team1_penalty_score,
            'team2_score' => $team2_penalty_score,
            'winnerId' => $winnerId,
            'winnerName' => $winnerName,
        ];
    }
    
    /**
     * Thực hiện một cú sút penalty
     */
    private function takePenaltyShot($attackingTeam, $defendingTeam)
    {
        $attackPower = $attackingTeam->attack;
        $defensePower = $defendingTeam->defense;
        
        // add mental to attack power and defense power
        $attackPower = $attackPower + ($attackingTeam->mental * 0.1);
        $defensePower = $defensePower + ($defendingTeam->mental * 0.1);
        
        $successChance = $defensePower > 0 ? ($attackPower / ($attackPower + $defensePower)) : 1;
        
        return rand(1, 100) <= $successChance ? 1 : 0;
    }
}