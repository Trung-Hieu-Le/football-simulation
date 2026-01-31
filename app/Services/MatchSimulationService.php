<?php

namespace App\Services;
use App\Services\FieldSimulationConstants;

class MatchSimulationService
{    
    /**
     * Xác định đội nào giao bóng dựa trên tình huống
     */
    public function getKickoffTeam($situation, $isExtraTime = false)
    {
        $startSituation = FieldSimulationConstants::START_SITUATION;
        $halftimeStartSituation = FieldSimulationConstants::HALFTIME_START_SITUATION;
        $extraTimeStartSituation = FieldSimulationConstants::EXTRATIME_START_SITUATION;
        $extraTimeHalftimeStartSituation = FieldSimulationConstants::EXTRATIME_HALFTIME_START_SITUATION;

        if ($isExtraTime) {
            if ($situation == $extraTimeStartSituation) {
                return 1; // Hiệp phụ 1: Team1 giao bóng
            } else if ($situation == $extraTimeHalftimeStartSituation) {
                return 2; // Hiệp phụ 2: Team2 giao bóng
            }
        } else {
            if ($situation == $startSituation) {
                return 1; // Hiệp 1: Team1 giao bóng
            } else if ($situation == $halftimeStartSituation) {
                return 2; // Hiệp 2: Team2 giao bóng
            }
        }
        return 1;
    }

    /**
     * Tính toán chỉ số đội với form và meta factors
     */
    public function calculateTeamStats($team, $seasonMeta, $isSecondHalf = false)
    {
        $staminaFactor = $isSecondHalf ? (FieldSimulationConstants::STAMINA_FACTOR_BASE + ($team->stamina / FieldSimulationConstants::STAMINA_DIVISOR)) : 1;
        $formFactor = 1 + ($team->form / FieldSimulationConstants::FORM_DIVISOR);
        
        $stats = [
            'attack' => $team->attack * $formFactor,
            'defense' => $team->defense * $formFactor,
            'creative' => $team->creative * $formFactor,
            'control' => $team->control * $formFactor,
            'pace' => $team->pace * $formFactor,
            'stamina_factor' => $staminaFactor,
            'mental' => $team->mental,
            'discipline' => $team->discipline,
        ];
        
        $stats = $this->applyStaminaFactors($stats, $isSecondHalf);
        
        return $stats;
    }
    
    /**
     * Áp dụng hệ số stamina cho hiệp 2
     */
    private function applyStaminaFactors($stats, $isSecondHalf)
    {
        if ($isSecondHalf) {
            $stats['attack'] *= $stats['stamina_factor'];
            $stats['defense'] *= $stats['stamina_factor'];
            $stats['creative'] *= $stats['stamina_factor'];
            $stats['control'] *= $stats['stamina_factor'];
            $stats['pace'] *= $stats['stamina_factor'];
        }
        
        return $stats;
    }
    
    /**
     * Simulate fulltime (90 phút)
     */
    public function simulateFullTime($team1, $team2, $seasonMeta, &$matchData)
    {
        $startSituation = FieldSimulationConstants::START_SITUATION;
        $halftimeStartSituation = FieldSimulationConstants::HALFTIME_START_SITUATION;
        $totalSituationsFulltime = FieldSimulationConstants::TOTAL_SITUATIONS_FULLTIME;
        $situationsPerMinute = FieldSimulationConstants::SITUATIONS_PER_MINUTE;

        $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
        $currentTeam = 1;
        
        for ($situation = $startSituation; $situation <= $totalSituationsFulltime; $situation++) {
            $time = (int)ceil($situation / $situationsPerMinute);
            
            if ($situation == $startSituation || $situation == $halftimeStartSituation) {
                $currentTeam = $this->getKickoffTeam($situation, false);
                $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
            }
            
            $isSecondHalf = $situation > $halftimeStartSituation;
            
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
            
            $goalResult = $this->processSituation(
                $fieldPosition,
                $currentTeam,
                $team1Stats,
                $team2Stats,
                $team1,
                $team2,
                $time,
                $matchData,
                $seasonMeta
            );
            
            if ($goalResult['goal']) {
                $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
                $currentTeam = $goalResult['goalScoredBy'] == 1 ? 2 : 1; // Đội bị ghi bàn giao bóng
            }
        }
    }
    
    /**
     * Simulate extra time (30 phút = 90 tình huống)
     */
    public function simulateExtraTime($team1, $team2, $seasonMeta, &$matchData)
    {
        $extraTimeStartSituation = FieldSimulationConstants::EXTRATIME_START_SITUATION;
        $extraTimeEndSituation = FieldSimulationConstants::EXTRATIME_END_SITUATION;
        $situationsPerMinute = FieldSimulationConstants::SITUATIONS_PER_MINUTE;
        $extraTimeHalftimeStartSituation = FieldSimulationConstants::EXTRATIME_HALFTIME_START_SITUATION;

        $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
        $currentTeam = 1;
        
        for ($situation = $extraTimeStartSituation; $situation <= $extraTimeEndSituation; $situation++) {
            $time = 90 + (int)ceil(($situation - $extraTimeStartSituation + 1) / $situationsPerMinute);
            
            if ($situation == $extraTimeStartSituation || $situation == $extraTimeHalftimeStartSituation) {
                $currentTeam = $this->getKickoffTeam($situation, true);
                $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
            }
            
            $isSecondHalf = $situation > $extraTimeHalftimeStartSituation;
            
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
            
            $goalResult = $this->processSituation(
                $fieldPosition,
                $currentTeam,
                $team1Stats,
                $team2Stats,
                $team1,
                $team2,
                $time,
                $matchData,
                $seasonMeta
            );
            
            if ($goalResult['goal']) {
                $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
                $currentTeam = $goalResult['goalScoredBy'] == 1 ? 2 : 1; // Đội bị ghi bàn giao bóng
            }
        }
    }
    
    /**
     * Xử lý một tình huống
     * Sử dụng con trỏ cho fieldPosition, currentTeam và seasonMeta
     */
    /**
     * Kiểm tra xem bóng có đang ở phía sân đối phương (có thể tấn công)
     * @param int $fieldPosition Vị trí bóng (0-10)
     * @param int $currentTeam Team đang cầm bóng (1 hoặc 2)
     * @return bool true nếu bóng ở phía sân đối phương
     */
    private function isBallOnOpponentSide($fieldPosition, $currentTeam)
    {
        if ($currentTeam == 1) {
            // Team 1 tấn công → bóng phải ở phía sân Team 2 (position >= 5)
            return $fieldPosition >= FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
        } else {
            // Team 2 tấn công → bóng phải ở phía sân Team 1 (position <= 5)
            return $fieldPosition <= FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
        }
    }
    
    /**
     * Tính khoảng cách đến khung thành đối phương
     * @param int $fieldPosition Vị trí bóng (0-10)
     * @param int $currentTeam Team đang tấn công (1 hoặc 2)
     * @return int Khoảng cách đến goal đối phương
     */
    private function getDistanceToGoal($fieldPosition, $currentTeam)
    {
        if ($currentTeam == 1) {
            // Team1 tấn công về phía goal Team2 (vị trí 10)
            return FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM2 - $fieldPosition;
        } else {
            // Team2 tấn công về phía goal Team1 (vị trí 0)
            return $fieldPosition - FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM1;
        }
    }
    
    /**
     * Xử lý tình huống bóng
     * Logic: Foul → MoveBall → Shot (nếu ở vị trí phù hợp)
     */
    private function processSituation(&$fieldPosition, &$currentTeam, $team1Stats, $team2Stats, 
                                     $team1, $team2, $time, &$matchData, &$seasonMeta)
    {
        $result = [
            'goal' => false,
            'goalScoredBy' => null,
            'isPenalty' => false
        ];
        
        // Xác định team tấn công và phòng thủ
        $attackingTeam = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingTeam = $currentTeam == 1 ? $team2Stats : $team1Stats;
        
        // 1. Kiểm tra foul (discipline)
        $defendingDiscipline = $defendingTeam['discipline'];
        $foulThreshold = FieldSimulationConstants::BASE_FOUL_CHANCE - ($defendingDiscipline / FieldSimulationConstants::FOUL_DISCIPLINE_DIVISOR);
        $foulThreshold = max(FieldSimulationConstants::FOUL_CHANCE_MIN, min(FieldSimulationConstants::FOUL_CHANCE_MAX, $foulThreshold));
        
        if (rand(1, 100) <= $foulThreshold) {
            return $this->handleFoul($fieldPosition, $currentTeam, $team1Stats, $team2Stats, 
                                    $team1, $team2, $time, $matchData, $seasonMeta);
        }
        
        // 2. MoveBall: possession, pressing, counter attack
        $this->moveBall($fieldPosition, $currentTeam, $attackingTeam, $defendingTeam, $seasonMeta);
        
        // 3. Kiểm tra quyết định sút (chỉ khi ở phía sân đối phương và gần goal)
        $isOnOpponentSide = $this->isBallOnOpponentSide($fieldPosition, $currentTeam);
        
        if ($isOnOpponentSide) {
            // Kiểm tra xem có ở vị trí shooting không
            $canShoot = false;
            if ($currentTeam == 1) {
                // Team 1 tấn công → position 8, 9, 10 (gần goal Team 2)
                $canShoot = $fieldPosition >= FieldSimulationConstants::FIELD_POSITION_FINAL_THIRD_TEAM2;
            } else {
                // Team 2 tấn công → position 0, 1, 2 (gần goal Team 1)
                $canShoot = $fieldPosition <= FieldSimulationConstants::FIELD_POSITION_FINAL_THIRD_TEAM1;
            }
            
            if ($canShoot) {
                $distanceToGoal = $this->getDistanceToGoal($fieldPosition, $currentTeam);
                $shootDecisionChance = FieldSimulationConstants::SHOOT_DECISION_BASE_CHANCE + 
                                       ($distanceToGoal * FieldSimulationConstants::SHOOT_DECISION_DISTANCE_BONUS) +
                                       ($attackingTeam['attack'] * FieldSimulationConstants::SHOOT_DECISION_ATTACK_MULTIPLIER) + 
                                       ($attackingTeam['mental'] * FieldSimulationConstants::SHOOT_DECISION_MENTAL_MULTIPLIER);
                
                // Meta: High risk - tăng shot chance
                if ($seasonMeta === 'high_risk') {
                    $shootDecisionChance += FieldSimulationConstants::META_HIGH_RISK_ATTACK_BONUS;
                }
                
                $shootDecisionChance = min(FieldSimulationConstants::SHOOT_DECISION_MAX, $shootDecisionChance);
                
                if (rand(1, 100) <= $shootDecisionChance) {
                    return $this->handleShot($fieldPosition, $currentTeam, $team1Stats, $team2Stats, 
                                            $team1, $team2, $time, $matchData, $seasonMeta);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * MoveBall: possession, pressing, counter attack
     * Team 1 tấn công → tăng position (0→10)
     * Team 2 tấn công → giảm position (10→0)
     */
    private function moveBall(&$fieldPosition, &$currentTeam, $attackingTeam, $defendingTeam, &$seasonMeta)
    {
        // Tính possession chance (control vs control + pace)
        $attackingPossessionPower = $attackingTeam['control'];
        $defendingPossessionPower = $defendingTeam['control'] + ($defendingTeam['pace'] * FieldSimulationConstants::PACE_PRESSING_MULTIPLIER);
        
        // Meta effects
        if ($seasonMeta === 'possession') {
            $attackingPossessionPower += FieldSimulationConstants::META_POSSESSION_BONUS_CHANCE;
        }
        if ($seasonMeta === 'build_up') {
            $attackingPossessionPower += FieldSimulationConstants::META_BUILD_UP_CONTROL_BONUS;
        }
        if ($seasonMeta === 'pressing') {
            $defendingPossessionPower += FieldSimulationConstants::META_PRESSING_BONUS_CHANCE;
        }
        if ($seasonMeta === 'high_line') {
            $defendingPossessionPower += FieldSimulationConstants::META_HIGH_LINE_PRESSING_BONUS;
        }
        
        $totalPower = $attackingPossessionPower + $defendingPossessionPower;
        $possessionChance = ($totalPower == 0) 
            ? FieldSimulationConstants::POSSESSION_BASE_CHANCE 
            : ($attackingPossessionPower / $totalPower) * 100;
        
        if (rand(1, 100) <= $possessionChance) {
            // AttackTeam giữ được bóng → moveball lên (creative)
            $creativePower = $attackingTeam['creative'];
            $defenseResistance = $defendingTeam['defense'] + ($defendingTeam['control'] * FieldSimulationConstants::CONTROL_DEFENSE_MULTIPLIER);
            
            if ($seasonMeta === 'low_block') {
                $defenseResistance += FieldSimulationConstants::META_LOW_BLOCK_DEFENSE_BONUS;
            }
            
            $moveSuccessChance = ($creativePower / ($creativePower + $defenseResistance)) * 100;
            
            if ($seasonMeta === 'tiki-taka') {
                $moveSuccessChance += FieldSimulationConstants::META_TIKI_TAKA_CREATIVE_BONUS;
                $moveSuccessChance = min(100, $moveSuccessChance);
            }
            
            if (rand(1, 100) <= $moveSuccessChance) {
                // Moveball thành công
                $moveDistance = FieldSimulationConstants::MOVE_DISTANCE_NORMAL;
                
                if ($seasonMeta === 'long_ball' && rand(1, 100) <= FieldSimulationConstants::META_LONG_BALL_CHANCE) {
                    $moveDistance = FieldSimulationConstants::META_LONG_BALL_MOVE_DISTANCE;
                } elseif ($creativePower > FieldSimulationConstants::CREATIVE_BONUS_THRESHOLD && 
                          rand(1, 100) <= FieldSimulationConstants::CREATIVE_BONUS_CHANCE) {
                    $moveDistance = FieldSimulationConstants::MOVE_DISTANCE_FAST;
                }
                
                // Team 1 tấn công → tăng position (0→10)
                // Team 2 tấn công → giảm position (10→0)
                if ($currentTeam == 1) {
                    $fieldPosition = min(FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM2, (int)$fieldPosition + $moveDistance);
                } else {
                    $fieldPosition = max(FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM1, (int)$fieldPosition - $moveDistance);
                }
            }
        } else {
            // DefendingTeam có thể pressing và cướp bóng
            $this->handlePressingAndCounterAttack($fieldPosition, $currentTeam, $attackingTeam, $defendingTeam, $seasonMeta);
        }
    }
    
    /**
     * Xử lý pressing và counter attack
     * Khi defendingTeam không giữ được possession, có thể pressing để cướp bóng
     * Logic: Khi ở sân nhà (xa goal đối phương) thì khó cướp bóng hơn
     */
    private function handlePressingAndCounterAttack(&$fieldPosition, &$currentTeam, $attackingTeam, $defendingTeam, &$seasonMeta)
    {
        // Kiểm tra xem bóng có ở sân nhà của attackingTeam không
        $isOnHomeSide = false;
        $distanceToOpponentGoal = 0;
        
        if ($currentTeam == 1) {
            // Team 1 tấn công → sân nhà là position 0-5, goal đối phương ở position 10
            $isOnHomeSide = $fieldPosition <= FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
            $distanceToOpponentGoal = FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM2 - $fieldPosition;
        } else {
            // Team 2 tấn công → sân nhà là position 5-10, goal đối phương ở position 0
            $isOnHomeSide = $fieldPosition >= FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
            $distanceToOpponentGoal = $fieldPosition - FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM1;
        }
        
        // Tính pressing power (control + pace + discipline)
        $pressingPower = $defendingTeam['control'] + 
                        ($defendingTeam['pace'] * FieldSimulationConstants::PACE_PRESSING_MULTIPLIER) +
                        ($defendingTeam['discipline'] * 0.2); // Discipline hỗ trợ pressing
        
        // Meta effects
        if ($seasonMeta === 'pressing') {
            $pressingPower += FieldSimulationConstants::META_PRESSING_BONUS_CHANCE;
        }
        if ($seasonMeta === 'high_line') {
            $pressingPower += FieldSimulationConstants::META_HIGH_LINE_PRESSING_BONUS;
        }
        
        // AttackingTeam resistance (control + creative)
        $attackingResistance = $attackingTeam['control'] + ($attackingTeam['creative'] * 0.3);
        
        // Tính pressing chance
        $totalPower = $pressingPower + $attackingResistance;
        $pressingChance = ($totalPower == 0) 
            ? 50 
            : ($pressingPower / $totalPower) * 100;
        
        // Penalty khi ở sân nhà (xa goal đối phương)
        // Càng xa goal đối phương (distanceToOpponentGoal lớn) → càng khó cướp bóng
        if ($isOnHomeSide && $distanceToOpponentGoal > FieldSimulationConstants::FIELD_POSITION_MIDFIELD) {
            // Ở sân nhà và xa goal đối phương → giảm pressing chance
            $distancePenalty = ($distanceToOpponentGoal - FieldSimulationConstants::FIELD_POSITION_MIDFIELD) * 
                             FieldSimulationConstants::PRESSING_DISTANCE_PENALTY_MULTIPLIER;
            $pressingChance -= FieldSimulationConstants::PRESSING_BASE_PENALTY + $distancePenalty;
            $pressingChance = max(0, $pressingChance); // Không được âm
        }
        
        if (rand(1, 100) <= $pressingChance) {
            // DefendingTeam pressing thành công → cướp bóng
            $currentTeam = 3 - $currentTeam; // Đổi team
            
            // Kiểm tra Counter Attack (pace)
            $pacePower = $defendingTeam['pace'];
            $counterChance = ($pacePower / 100) * FieldSimulationConstants::COUNTER_CHANCE_BASE_MULTIPLIER * 100;
            $counterChance = min(FieldSimulationConstants::COUNTER_CHANCE_MAX, max(FieldSimulationConstants::COUNTER_CHANCE_MIN, $counterChance));
            
            // Meta: Counter - tăng counter chance
            if ($seasonMeta === 'counter') {
                $counterChance += 10; // Tăng thêm 10%
                $counterChance = min(100, $counterChance);
            }
            
            if (rand(1, 100) <= $counterChance) {
                // Counter attack
                $maxDistance = ($seasonMeta === 'counter') 
                    ? FieldSimulationConstants::META_COUNTER_MAX_DISTANCE 
                    : FieldSimulationConstants::COUNTER_DISTANCE_MAX;
                
                $moveDistance = min(
                    $maxDistance, 
                    max(
                        FieldSimulationConstants::COUNTER_DISTANCE_MIN, 
                        (int)($pacePower / FieldSimulationConstants::COUNTER_DISTANCE_DIVISOR)
                    )
                );
                
                // currentTeam đã đổi sang defendingTeam (team cướp bóng)
                // Team 1 phản công → tăng position (0→10)
                // Team 2 phản công → giảm position (10→0)
                if ($currentTeam == 1) {
                    $fieldPosition = min(FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM2, (int)$fieldPosition + $moveDistance);
                } else {
                    $fieldPosition = max(FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM1, (int)$fieldPosition - $moveDistance);
                }
            }
            // Nếu không có counter attack, bóng vẫn ở vị trí hiện tại, team đã đổi
        }
        // Nếu pressing không thành công, bóng vẫn ở vị trí hiện tại, team không đổi
    }
    
    /**
     * Xử lý shot
     * Khi shot trượt → đổi team và về midfield
     */
    private function handleShot(&$fieldPosition, &$currentTeam, $team1Stats, $team2Stats, 
                               $team1, $team2, $time, &$matchData, &$seasonMeta)
    {
        $shootingTeam = $currentTeam;
        $shootingTeamName = $shootingTeam == 1 ? $team1->name : $team2->name;
        
        if ($shootingTeam == 1) {
            $matchData['team1_shots']++;
        } else {
            $matchData['team2_shots']++;
        }
        
        $attackingTeam = $shootingTeam == 1 ? $team1Stats : $team2Stats;
        $defendingTeam = $shootingTeam == 1 ? $team2Stats : $team1Stats;
        
        $distanceToGoal = $this->getDistanceToGoal($fieldPosition, $currentTeam);
        
        // Tính shot_on_target chance
        $onTargetBaseChance = FieldSimulationConstants::BASE_SHOT_ON_TARGET_CHANCE;
        $distanceBonus = (3 - $distanceToGoal) * FieldSimulationConstants::SHOT_DISTANCE_BONUS_MULTIPLIER;
        $attackBonus = $attackingTeam['attack'] * FieldSimulationConstants::SHOT_ATTACK_BONUS_MULTIPLIER;
        
        $onTargetChance = $onTargetBaseChance + $distanceBonus + $attackBonus;
        $onTargetChance = min(FieldSimulationConstants::SHOT_ON_TARGET_MAX, max(FieldSimulationConstants::SHOT_ON_TARGET_MIN, $onTargetChance));
        
        $isOnTarget = rand(1, 100) <= $onTargetChance;
        
        $result = [
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
            
            // Tính goal chance
            $attackPower = $attackingTeam['attack'] + ($attackingTeam['mental'] * FieldSimulationConstants::GOAL_MENTAL_MULTIPLIER);
            $defensePower = $defendingTeam['defense'] + ($defendingTeam['mental'] * FieldSimulationConstants::GOAL_MENTAL_MULTIPLIER);
            $goalChance = ($attackPower / ($attackPower + $defensePower)) * FieldSimulationConstants::BASE_GOAL_CHANCE;
            
            if (rand(1, 100) <= $goalChance) {
                if ($shootingTeam == 1) {
                    $matchData['team1_score']++;
                } else {
                    $matchData['team2_score']++;
                }
                
                $matchData['specialEvents'][] = "$time': GOAAAAAAAAAAAL by $shootingTeamName!";
                $result['goal'] = true;
                $result['goalScoredBy'] = $shootingTeam;
                // Sau khi ghi bàn → về midfield và đổi team
                $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
                $currentTeam = $shootingTeam == 1 ? 2 : 1;
            } else {
                // Shot trượt → đổi team và về midfield
                $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
                $currentTeam = $shootingTeam == 1 ? 2 : 1;
            }
        } else {
            // Shot không on target → đổi team và về midfield
            $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
            $currentTeam = $shootingTeam == 1 ? 2 : 1;
        }
        
        return $result;
    }
    
    /**
     * Xử lý foul
     * Foul ở sân đối phương → penalty/freekick
     * Foul ở sân nhà → bóng về midfield cho đội bạn
     */
    private function handleFoul(&$fieldPosition, &$currentTeam, $team1Stats, $team2Stats, 
                               $team1, $team2, $time, &$matchData, &$seasonMeta)
    {
        $foulingTeam = $currentTeam == 1 ? 2 : 1;
        $foulingTeamName = $foulingTeam == 1 ? $team1->name : $team2->name;
        
        if ($foulingTeam == 1) {
            $matchData['team1_fouls']++;
        } else {
            $matchData['team2_fouls']++;
        }

        $matchData['specialEvents'][] = "$time': Foul by $foulingTeamName!";
        
        $isOnOpponentSide = $this->isBallOnOpponentSide($fieldPosition, $currentTeam);
        
        if ($isOnOpponentSide) {
            // Foul ở phía sân đối phương → có thể penalty hoặc free kick
            $isPenaltyArea = false;
            if ($currentTeam == 1) {
                // Team 1 tấn công → penalty area đối phương là position 9, 10
                $isPenaltyArea = in_array($fieldPosition, [
                    FieldSimulationConstants::FIELD_POSITION_PENALTY_AREA_TEAM2,
                    FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM2
                ]);
            } else {
                // Team 2 tấn công → penalty area đối phương là position 0, 1
                $isPenaltyArea = in_array($fieldPosition, [
                    FieldSimulationConstants::FIELD_POSITION_PENALTY_AREA_TEAM1,
                    FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM1
                ]);
            }
            
            if ($isPenaltyArea && rand(1, 100) <= FieldSimulationConstants::PENALTY_CHANCE) {
                return $this->handlePenalty($fieldPosition, $currentTeam, $team1Stats, $team2Stats, $team1, $team2, $time, $matchData, $seasonMeta);
            }
            
            return $this->handleFreeKick($fieldPosition, $currentTeam, $team1Stats, $team2Stats, $team1, $team2, $time, $matchData, $seasonMeta);
        } else {
            // Foul ở phía sân nhà → bóng về midfield cho đội bạn
            $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
            return [
                'goal' => false,
                'goalScoredBy' => null,
                'isPenalty' => false
            ];
        }
    }
    
    /**
     * Xử lý free kick
     */
    private function handleFreeKick(&$fieldPosition, &$currentTeam, $team1Stats, $team2Stats, 
                                   $team1, $team2, $time, &$matchData, &$seasonMeta)
    {
        $attackingTeam = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingTeam = $currentTeam == 1 ? $team2Stats : $team1Stats;
        
        $distanceToGoal = $this->getDistanceToGoal($fieldPosition, $currentTeam);
        
        $result = [
            'goal' => false,
            'goalScoredBy' => null,
            'isPenalty' => false
        ];
        
        // Quyết định shot hay pass
        $baseShotChance = FieldSimulationConstants::FREEKICK_SHOT_BASE_CHANCE;
        $mentalBonus = $attackingTeam['mental'] * FieldSimulationConstants::FREEKICK_MENTAL_BONUS_MULTIPLIER;
        $creativeBonus = $attackingTeam['creative'] * FieldSimulationConstants::FREEKICK_CREATIVE_BONUS_MULTIPLIER;
        
        $shotChance = $baseShotChance + $mentalBonus + $creativeBonus;
        $shotChance = min(FieldSimulationConstants::FREEKICK_SHOT_CHANCE_MAX, max(FieldSimulationConstants::FREEKICK_SHOT_CHANCE_MIN, $shotChance));
        
        if (rand(1, 100) <= $shotChance) {
            // Shot
            $scoringTeam = $currentTeam;
            $scoringTeamName = $scoringTeam == 1 ? $team1->name : $team2->name;
            
            if ($scoringTeam == 1) {
                $matchData['team1_shots']++;
            } else {
                $matchData['team2_shots']++;
            }
            
            // Tính on_target
            $onTargetBase = FieldSimulationConstants::FREEKICK_ON_TARGET_BASE;
            $distanceBonus = (3 - $distanceToGoal) * FieldSimulationConstants::FREEKICK_DISTANCE_BONUS_MULTIPLIER;
            $attackBonus = $attackingTeam['attack'] * FieldSimulationConstants::FREEKICK_ATTACK_BONUS_MULTIPLIER;
            
            $onTargetChance = $onTargetBase + $distanceBonus + $attackBonus;
            $onTargetChance = min(FieldSimulationConstants::FREEKICK_ON_TARGET_MAX, max(FieldSimulationConstants::FREEKICK_ON_TARGET_MIN, $onTargetChance));
            
            $isOnTarget = rand(1, 100) <= $onTargetChance;
            
            if ($isOnTarget) {
                if ($scoringTeam == 1) {
                    $matchData['team1_shots_on_target']++;
                } else {
                    $matchData['team2_shots_on_target']++;
                }
                
                // Tính goal
                $attackPower = $attackingTeam['attack'] + ($attackingTeam['mental'] * FieldSimulationConstants::FREEKICK_GOAL_MENTAL_MULTIPLIER);
                $defensePower = $defendingTeam['defense'] + ($defendingTeam['mental'] * FieldSimulationConstants::FREEKICK_GOAL_DEFENSE_MENTAL_MULTIPLIER);
                $goalChance = ($attackPower / ($attackPower + $defensePower)) * FieldSimulationConstants::FREEKICK_GOAL_BASE;
                
                if (rand(1, 100) <= $goalChance) {
                    if ($scoringTeam == 1) {
                        $matchData['team1_score']++;
                    } else {
                        $matchData['team2_score']++;
                    }
                    
                    $matchData['specialEvents'][] = "$time': Free Kick GOAAAAAAAAAAAL by $scoringTeamName!";
                    $result['goal'] = true;
                    $result['goalScoredBy'] = $scoringTeam;
                }
            }
            
            // Sau free kick → về midfield và đổi team
            $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
            $currentTeam = $scoringTeam == 1 ? 2 : 1;
        } else {
            // Pass → Moveball
            if ($seasonMeta === 'long_ball' && rand(1, 100) <= FieldSimulationConstants::META_LONG_BALL_CHANCE) {
                $moveDistance = FieldSimulationConstants::META_LONG_BALL_MOVE_DISTANCE;
            } else {
                $moveDistance = rand(FieldSimulationConstants::FREEKICK_PASS_DISTANCE_MIN, FieldSimulationConstants::FREEKICK_PASS_DISTANCE_MAX);
            }
            
            // Team 1 tấn công → tăng position, Team 2 tấn công → giảm position
            if ($currentTeam == 1) {
                $fieldPosition = min(FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM2, (int)$fieldPosition + $moveDistance);
            } else {
                $fieldPosition = max(FieldSimulationConstants::FIELD_POSITION_GOAL_TEAM1, (int)$fieldPosition - $moveDistance);
            }
        }
        
        return $result;
    }
    
    /**
     * Xử lý penalty
     */
    private function handlePenalty(&$fieldPosition, &$currentTeam, $team1Stats, $team2Stats, $team1, $team2, $time, &$matchData, &$seasonMeta)
    {
        // currentTeam là team được hưởng penalty (team bị foul)
        $attackingTeam = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingTeam = $currentTeam == 1 ? $team2Stats : $team1Stats;
        
        $scoringTeam = $currentTeam;
        $scoringTeamName = $scoringTeam == 1 ? $team1->name : $team2->name;
        
        // 100% sẽ shot
        if ($scoringTeam == 1) {
            $matchData['team1_shots']++;
        } else {
            $matchData['team2_shots']++;
        }
        
        // Tính shot_on_target (attack + mental + creative)
        $onTargetBase = FieldSimulationConstants::PENALTY_ON_TARGET_BASE;
        $attackBonus = $attackingTeam['attack'] * FieldSimulationConstants::PENALTY_ATTACK_BONUS_MULTIPLIER;
        $mentalBonus = $attackingTeam['mental'] * FieldSimulationConstants::PENALTY_MENTAL_BONUS_MULTIPLIER;
        $creativeBonus = $attackingTeam['creative'] * FieldSimulationConstants::PENALTY_CREATIVE_BONUS_MULTIPLIER;
        
        $onTargetChance = $onTargetBase + $attackBonus + $mentalBonus + $creativeBonus;
        $onTargetChance = min(FieldSimulationConstants::PENALTY_ON_TARGET_MAX, max(FieldSimulationConstants::PENALTY_ON_TARGET_MIN, $onTargetChance));
        
        $isOnTarget = rand(1, 100) <= $onTargetChance;
        
        $result = [
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
            
            // Tính goal (attack + mental + creative vs defense + mental)
            $attackPower = $attackingTeam['attack'] + 
                          ($attackingTeam['mental'] * FieldSimulationConstants::PENALTY_GOAL_ATTACK_MENTAL_MULTIPLIER) + 
                          ($attackingTeam['creative'] * FieldSimulationConstants::PENALTY_GOAL_ATTACK_CREATIVE_MULTIPLIER);
            $defensePower = $defendingTeam['defense'] + ($defendingTeam['mental'] * FieldSimulationConstants::PENALTY_GOAL_DEFENSE_MENTAL_MULTIPLIER);
            $goalChance = ($attackPower / ($attackPower + $defensePower)) * FieldSimulationConstants::PENALTY_GOAL_BASE;
            
            if (rand(1, 100) <= $goalChance) {
                if ($scoringTeam == 1) {
                    $matchData['team1_score']++;
                } else {
                    $matchData['team2_score']++;
                }
                
                $matchData['specialEvents'][] = "$time': Penalty GOAAAAAAAAAAAL by $scoringTeamName!";
                $result['goal'] = true;
                $result['goalScoredBy'] = $scoringTeam;
                // Sau khi ghi bàn, đưa về midfield và đổi team
                $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
                $currentTeam = $scoringTeam == 1 ? 2 : 1;
            } else {
                $matchData['specialEvents'][] = "$time': Penalty saved!";
                // Không ghi bàn → về midfield và đổi team
                $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
                $currentTeam = $scoringTeam == 1 ? 2 : 1;
            }
        } else {
            $matchData['specialEvents'][] = "$time': Penalty saved!";
            // Không on target → về midfield và đổi team
            $fieldPosition = (int)FieldSimulationConstants::FIELD_POSITION_MIDFIELD;
            $currentTeam = $scoringTeam == 1 ? 2 : 1;
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
        $attackPower = $attackPower + ($attackingTeam->mental * FieldSimulationConstants::GOAL_MENTAL_MULTIPLIER);
        $defensePower = $defensePower + ($defendingTeam->mental * FieldSimulationConstants::GOAL_MENTAL_MULTIPLIER);
        
        $successChance = ($attackPower / ($attackPower + $defensePower)) * FieldSimulationConstants::PENALTY_GOAL_BASE;
        
        return rand(1, 100) <= $successChance ? 1 : 0;
    }
}