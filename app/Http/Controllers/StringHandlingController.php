<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StringhandlingController extends Controller
{

    /**
     * Reduces a string, deleting the repeated characters
     * @param string $string
     * @return 
     */
    public function reduceString($string){         
        # Define a stack to store characters
        $stack = [];
    
        # Iterate through the input string
        for ($i = 0; $i < strlen($string); $i++) {
            # Get the current character
            $currentChar = $string[$i];
    
            # If the stack is empty, or the current character is not the same as the last one pushed onto the stack
            # then push this character onto the stack
            if (empty($stack) || end($stack) !== $currentChar) {
                array_push($stack, $currentChar);
            } else {
                # If the current character is the same as the last one pushed onto the stack
                # then pop the last character from the stack
                array_pop($stack);
            }
        }
    
        # If the stack is empty after iterating through the string
        # then the string is "Empty String"
        if (empty($stack)) {
            $string = "Empty String";
        } else {
            # Otherwise, the string is the concatenation of the characters remaining on the stack
            $string = implode("", $stack);
        }
    
        # Return a JSON response with the resulting string
        return response()->json(['message' => 'The result is: '.$string], 200);
    }        
    
}