<?php

namespace App\Services;

class FieldSimulationConstants
{
    // Constants
    const SITUATIONS_PER_MINUTE = 5;
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
    const FIELD_POSITION_MIDFIELD_LOW_TEAM1 = 3;
    const FIELD_POSITION_MIDFIELD_HIGH_TEAM1 = 4;
    const FIELD_POSITION_MIDFIELD = 5;
    const FIELD_POSITION_MIDFIELD_LOW_TEAM2 = 6;
    const FIELD_POSITION_MIDFIELD_HIGH_TEAM2 = 7;
    const FIELD_POSITION_FINAL_THIRD_TEAM2 = 8;
    const FIELD_POSITION_PENALTY_AREA_TEAM2 = 9;
    const FIELD_POSITION_GOAL_TEAM2 = 10;
    
    // Shooting positions (có thể quyết định sút)
    const SHOOTING_POSITIONS = [0, 1, 2, 8, 9, 10];
    
    // Meta gameplay effects (ảnh hưởng trực tiếp vào logic)
    const META_COUNTER_MAX_DISTANCE = 4; // Counter attack: tối đa 4 move
    const META_LONG_BALL_MOVE_DISTANCE = 3; // Long ball: chuyền lên 3 move
    const META_LONG_BALL_CHANCE = 25; // 25% chance long ball pass
    const META_PRESSING_BONUS_CHANCE = 10; // Pressing: +10% cơ hội cướp bóng
    const META_POSSESSION_BONUS_CHANCE = 8; // Possession: +8% possession chance
    const META_TIKI_TAKA_CREATIVE_BONUS = 15; // Tiki-taka: +15% creative moveball success
    const META_BUILD_UP_CONTROL_BONUS = 12; // Build up: +12% control possession
    const META_HIGH_RISK_ATTACK_BONUS = 8; // High risk: +8% attack shot chance
    const META_LOW_BLOCK_DEFENSE_BONUS = 12; // Low block: +12% defense resistance
    const META_HIGH_LINE_PRESSING_BONUS = 8; // High line: +8% pressing chance
    
    // Stamina factors
    const STAMINA_FACTOR_BASE = 0.5; // 50% tỉ lệ stamina ảnh hưởng đến stats
    const STAMINA_DIVISOR = 200;
    const FORM_DIVISOR = 1000;
    
    // Foul constants
    const BASE_FOUL_CHANCE = 5; // 5% base
    const FOUL_DISCIPLINE_DIVISOR = 50;
    const FOUL_CHANCE_MIN = 0;
    const FOUL_CHANCE_MAX = 10;
    const PENALTY_CHANCE = 50; // 50% chance penalty khi foul ở vị trí 1 hoặc 9
    
    // Moveball constants
    const MOVE_DISTANCE_NORMAL = 1;
    const MOVE_DISTANCE_FAST = 2;
    const CREATIVE_BONUS_THRESHOLD = 70; // Creative > 70 có thể moveball +2
    const CREATIVE_BONUS_CHANCE = 25; // 25% chance moveball +2
    const PACE_PRESSING_MULTIPLIER = 0.5; // Pace hỗ trợ pressing 50%
    const CONTROL_DEFENSE_MULTIPLIER = 0.3; // Control hỗ trợ defense 30%
    
    // Possession constants
    const POSSESSION_BASE_CHANCE = 50; // Base 50% nếu total = 0
    
    // Pressing constants
    const PRESSING_DISTANCE_PENALTY_MULTIPLIER = 5; // Mỗi đơn vị xa goal đối phương, giảm pressing chance 5%
    const PRESSING_BASE_PENALTY = 20; // Penalty cơ bản khi ở sân nhà (20%)
    
    // Counter attack constants
    const COUNTER_CHANCE_BASE_MULTIPLIER = 0.4; // Pace * 0.4 = counter chance
    const COUNTER_CHANCE_MIN = 15; // Tối thiểu 15%
    const COUNTER_CHANCE_MAX = 50; // Tối đa 50%
    const COUNTER_DISTANCE_DIVISOR = 50; // Pace / 50 = move distance
    const COUNTER_DISTANCE_MIN = 1; // Tối thiểu 1 position
    const COUNTER_DISTANCE_MAX = 3; // Tối đa 3 position
    
    // Shot constants
    const BASE_SHOT_ON_TARGET_CHANCE = 40; // Base 40%
    const SHOT_DISTANCE_BONUS_MULTIPLIER = 5; // Mỗi vị trí gần hơn +5%
    const SHOT_ATTACK_BONUS_MULTIPLIER = 0.1; // Attack * 0.1
    const SHOT_ON_TARGET_MIN = 20; // Tối thiểu 20%
    const SHOT_ON_TARGET_MAX = 85; // Tối đa 85%
    
    // Goal constants
    const BASE_GOAL_CHANCE = 30; // Base 30%
    const GOAL_MENTAL_MULTIPLIER = 0.1; // Mental * 0.1 hỗ trợ
    
    // Counter after shot constants
    const COUNTER_AFTER_SHOT_CHANCE = 50; // 50% chance cướp bóng sau shot trượt
    
    // Shoot decision constants
    const SHOOT_DECISION_BASE_CHANCE = 8; // Base 8%
    const SHOOT_DECISION_DISTANCE_BONUS = 5; // Mỗi vị trí gần hơn +5%
    const SHOOT_DECISION_ATTACK_MULTIPLIER = 0.2; // Attack * 0.2
    const SHOOT_DECISION_MENTAL_MULTIPLIER = 0.15; // Mental * 0.15
    const SHOOT_DECISION_MAX = 40; // Tối đa 40%
    
    // Freekick constants
    const FREEKICK_SHOT_BASE_CHANCE = 25; // Base 25%
    const FREEKICK_MENTAL_BONUS_MULTIPLIER = 0.1; // Mental * 0.1
    const FREEKICK_CREATIVE_BONUS_MULTIPLIER = 0.2; // Creative * 0.2
    const FREEKICK_SHOT_CHANCE_MIN = 15; // Tối thiểu 15%
    const FREEKICK_SHOT_CHANCE_MAX = 50; // Tối đa 50%
    
    const FREEKICK_ON_TARGET_BASE = 10; // Base 10%
    const FREEKICK_DISTANCE_BONUS_MULTIPLIER = 2; // Mỗi vị trí gần hơn +2%
    const FREEKICK_ATTACK_BONUS_MULTIPLIER = 0.1; // Attack * 0.1
    const FREEKICK_ON_TARGET_MIN = 5; // Tối thiểu 5%
    const FREEKICK_ON_TARGET_MAX = 40; // Tối đa 40%
    
    const FREEKICK_GOAL_BASE = 5; // Base 5%
    const FREEKICK_GOAL_MENTAL_MULTIPLIER = 0.1; // Mental * 0.1 cho attack
    const FREEKICK_GOAL_DEFENSE_MENTAL_MULTIPLIER = 0.05; // Mental * 0.05 cho defense
    
    const FREEKICK_PASS_DISTANCE_MIN = 1;
    const FREEKICK_PASS_DISTANCE_MAX = 2;
    
    // Penalty constants
    const PENALTY_ON_TARGET_BASE = 80; // Base 80%
    const PENALTY_ATTACK_BONUS_MULTIPLIER = 0.1; // Attack * 0.1
    const PENALTY_MENTAL_BONUS_MULTIPLIER = 0.15; // Mental * 0.15
    const PENALTY_CREATIVE_BONUS_MULTIPLIER = 0.05; // Creative * 0.05
    const PENALTY_ON_TARGET_MIN = 70; // Tối thiểu 70%
    const PENALTY_ON_TARGET_MAX = 95; // Tối đa 95%
    
    const PENALTY_GOAL_BASE = 70; // Base 70%
    const PENALTY_GOAL_ATTACK_MENTAL_MULTIPLIER = 0.15; // Mental * 0.15 cho attack
    const PENALTY_GOAL_ATTACK_CREATIVE_MULTIPLIER = 0.05; // Creative * 0.05 cho attack
    const PENALTY_GOAL_DEFENSE_MENTAL_MULTIPLIER = 0.1; // Mental * 0.1 cho defense
}

