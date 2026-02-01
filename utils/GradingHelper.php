<?php
/**
 * Grading Helper
 * Provides functions for grade and GPA calculations
 */

class GradingHelper {
    private static $gradingScheme = array(
        'A+' => array('min' => 90, 'max' => 100, 'gpa' => 4.0, 'is_active' => 'Excellent'),
        'A'  => array('min' => 85, 'max' => 89.99, 'gpa' => 3.75, 'is_active' => 'Excellent'),
        'A-' => array('min' => 80, 'max' => 84.99, 'gpa' => 3.5, 'is_active' => 'Excellent'),
        'B+' => array('min' => 75, 'max' => 79.99, 'gpa' => 3.0, 'is_active' => 'Good'),
        'B'  => array('min' => 70, 'max' => 74.99, 'gpa' => 2.75, 'is_active' => 'Good'),
        'B-' => array('min' => 65, 'max' => 69.99, 'gpa' => 2.5, 'is_active' => 'Good'),
        'C+' => array('min' => 60, 'max' => 64.99, 'gpa' => 2.0, 'is_active' => 'Satisfactory'),
        'C'  => array('min' => 55, 'max' => 59.99, 'gpa' => 1.75, 'is_active' => 'Satisfactory'),
        'C-' => array('min' => 50, 'max' => 54.99, 'gpa' => 1.5, 'is_active' => 'Satisfactory'),
        'D'  => array('min' => 45, 'max' => 49.99, 'gpa' => 1.0, 'is_active' => 'Pass'),
        'F'  => array('min' => 0, 'max' => 44.99, 'gpa' => 0.0, 'is_active' => 'Fail')
    );
    
    /**
     * Get grade information from score
     * @param float $score - The percentage score (0-100)
     * @return array - Array with grade, gpa, and status
     */
    public static function getGradeFromScore($score) {
        foreach(self::$gradingScheme as $grade => $details) {
            if($score >= $details['min'] && $score <= $details['max']) {
                return array(
                    'grade' => $grade,
                    'gpa' => $details['gpa'],
                    'is_active' => $details['is_active'],
                    'percentage' => $score
                );
            }
        }
        // Default to F if no match
        return array(
            'grade' => 'F',
            'gpa' => 0.0,
            'is_active' => 'Fail',
            'percentage' => $score
        );
    }
    
    /**
     * Get color for grade display
     * @param string $grade - The letter grade
     * @return string - CSS color code
     */
    public static function getGradeColor($grade) {
        if(in_array($grade, ['A+', 'A', 'A-'])) {
            return '#28a745'; // Green - Excellent
        } elseif(in_array($grade, ['B+', 'B', 'B-'])) {
            return '#007bff'; // Blue - Good
        } elseif(in_array($grade, ['C+', 'C', 'C-'])) {
            return '#ffc107'; // Yellow - Satisfactory
        } elseif($grade == 'D') {
            return '#ff9800'; // Orange - Pass
        } else {
            return '#dc3545'; // Red - Fail
        }
    }
    
    /**
     * Check if score is passing
     * @param float $score - The percentage score
     * @return bool - True if passing (>= 50%)
     */
    public static function isPassing($score) {
        return $score >= 50;
    }
    
    /**
     * Calculate cumulative GPA from multiple scores
     * @param array $scores - Array of percentage scores
     * @return float - Cumulative GPA
     */
    public static function calculateCumulativeGPA($scores) {
        if(empty($scores)) return 0.0;
        
        $totalGPA = 0;
        foreach($scores as $score) {
            $gradeInfo = self::getGradeFromScore($score);
            $totalGPA += $gradeInfo['gpa'];
        }
        
        return round($totalGPA / count($scores), 2);
    }
    
    /**
     * Get all grading scheme
     * @return array - Complete grading scheme
     */
    public static function getGradingScheme() {
        return self::$gradingScheme;
    }
    
    /**
     * Format grade display with color
     * @param float $score - The percentage score
     * @return string - HTML formatted grade display
     */
    public static function formatGradeDisplay($score) {
        $gradeInfo = self::getGradeFromScore($score);
        $color = self::getGradeColor($gradeInfo['grade']);
        
        return sprintf(
            '<span style="color: %s; font-weight: 700;">%s</span> <span style="color: #6c757d;">(GPA: %s)</span>',
            $color,
            $gradeInfo['grade'],
            $gradeInfo['gpa']
        );
    }
    
    /**
     * Get grade badge HTML
     * @param float $score - The percentage score
     * @return string - HTML badge
     */
    public static function getGradeBadge($score) {
        $gradeInfo = self::getGradeFromScore($score);
        $color = self::getGradeColor($gradeInfo['grade']);
        
        return sprintf(
            '<span style="background: %s; color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">%s - %s%%</span>',
            $color,
            $gradeInfo['grade'],
            round($score, 1)
        );
    }
    
    /**
     * Get performance message based on grade
     * @param float $score - The percentage score
     * @return string - Performance message
     */
    public static function getPerformanceMessage($score) {
        $gradeInfo = self::getGradeFromScore($score);
        
        $messages = array(
            'Excellent' => 'Outstanding performance! Keep up the excellent work!',
            'Good' => 'Good job! You\'re doing well.',
            'Satisfactory' => 'Satisfactory performance. There\'s room for improvement.',
            'Pass' => 'You passed, but consider reviewing the material for better understanding.',
            'Fail' => 'Unfortunately, you did not pass. Please review the material and try again.'
        );
        
        return $messages[$gradeInfo['is_active']] ?? 'Keep working hard!';
    }
}
?>
